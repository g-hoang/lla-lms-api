<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityComponentRequest;
use App\Http\Requests\CopyComponentRequest;
use App\Linkup\Facades\Search;
use App\Models\ActivityComponent;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Resources\ActivityComponent as ActivityComponentResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Mews\Purifier\Facades\Purifier;
use Mockery\Exception;

class ActivityComponentController extends ApiController
{
    /**
     * Display a listing of activities by lesson.
     *
     * @param Request $request Http Request
     *
     * @param $activity_id
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, $activity_id)
    {
        $this->authorize('activity.list');

        $request->request->add(['activity_id' => $activity_id]);

        $components = Search::components($request);

        if ($size = $request->get('pageSize')) {
            return ActivityComponentResource::collection($components->paginate($size));
        }

        return ActivityComponentResource::collection($components->get());
    }


    /**
     * Store a newly created activity.
     *
     * @param ActivityComponentRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(ActivityComponentRequest $request)
    {
        $this->authorize('activity.create');

        try {
            $component = ActivityComponent::create(
                [
                    'activity_id' => $request->activity_id,
                    'component_type' => $request->component_type,
                    'data' => $request->data,
                ]
            );

            $data = $this->updateComponentMetaData($component, $request);

            $component->fill(['data' => $data])->save();

        } catch (QueryException $e) {
            return $this->respondWithError("Record creation failed. code: " . $e->getMessage());
        }

        $msg = "Component created";
        return $this->respondCreated($msg, new ActivityComponentResource($component));
    }

    /**
     * Show activity
     *
     * @param $id
     *
     * @return ActivityComponentResource|mixed
     * @throws AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('activity.view');

        if ($component = ActivityComponent::find($id)) {
            if (Auth::user()->cant('view', $component)) {
                throw new AuthenticationException();
            }

            return new ActivityComponentResource($component);
        }

        return $this->respondNotFound('Component Not Found');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param ActivityComponentRequest $request
     * @param ActivityComponent $component
     * @return mixed
     * @throws AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(ActivityComponentRequest $request, ActivityComponent $component)
    {
        $this->authorize('activity.update');

        if (Auth::user()->cant('update', $component)) {
            throw new AuthenticationException();
        }

        $data = $this->updateComponentMetaData($component, $request);

        if (!$data) {
            $this->respondWithError("File upload failed. Please try again.");
        };
        
        $component->fill(['data' => $data]);

        $component->saveOrFail();

        return $this->respondSuccess('Component updated', new ActivityComponentResource($component));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ActivityComponent $component
     * @return mixed
     * @throws AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(ActivityComponent $component)
    {
        $this->authorize('activity.delete');

        if (Auth::user()->cant('delete', $component)) {
            throw new AuthenticationException();
        }

        $toDel = [];

        if ($component->component_type == 'TEXT_INPUT') {
            $component->load('activity');

            $lesson_id = $component->activity->lesson_id;
            $components = ActivityComponent::with(['Activity'])
                ->where('component_type', 'TEXT_OUTPUT')
                ->whereHas('Activity', function ($q) use ($lesson_id) {
                    $q->where('lesson_id', $lesson_id);
                })
                ->get();

            foreach ($components as $comp) {
                if (isset($comp->data['id']) &&  $comp->data['id'] == $component->id) {
                    $toDel[] = $comp;
                }
            }
        }

        try {
            $component->delete();
            foreach ($toDel as $c) {
                $c->delete();
            }
            return $this->respondSuccess('Component deleted');
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateOrder(Request $request)
    {
        $list = $request->get('list', []);
        $this->authorize('activity.update');

        foreach ($list as $i => $id) {
            $activity = ActivityComponent::find($id);
            $activity->order = $i + 1;
            $activity->saveOrFail();
        }

        return $this->respondSuccess('Order updated');
    }

    /**
     * @param $id
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getTextInputIdsForLesson($id)
    {
        $this->authorize('activity.view');

        $components = ActivityComponent::with(['Activity'])
            ->where('component_type', 'TEXT_INPUT')
            ->whereHas('Activity', function ($q) use ($id) {
                $q->where('lesson_id', $id);
            })
            ->get()
            ->toArray();

        $out=[];
        foreach ($components as $component) {
            $out[] = [
                'id' => isset($component['data']['id']) ? $component['data']['id'] : '',
                'label' => isset($component['data']['label']) ? $component['data']['label'] : '',
                'title' => isset($component['data']['title']) ? $component['data']['title'] : ''
            ];
        }

        return $out;
    }

    /**
     * @param ActivityComponent $component
     * @param $image
     * @param $data
     * @return bool|mixed
     */
    public function uploadImage(ActivityComponent $component, $image, $data)
    {
        $data = json_decode($data, true);
        $s3 = Storage::disk('s3');
        $file_name = preg_replace("/[^A-Za-z0-9\_\-\.]/", '-', $image->getClientOriginalName());
        $path_prefix = 'components/' . $component->id;

        $ret = $s3->put($path_prefix . "-full-" . $file_name, file_get_contents($image));

        $img = Image::make($image->getRealPath());
        $img->getCore()->setInterlaceScheme(\Imagick::INTERLACE_PLANE);

        $img->resize(768, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $ret = $s3->put($path_prefix . "-small-" . $file_name, $img->stream('jpg', '75')->__toString());

        $img = Image::make($image->getRealPath());
        $img->getCore()->setInterlaceScheme(\Imagick::INTERLACE_PLANE);

        $img->resize(1024, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $ret = $s3->put($path_prefix . "-medium-" . $file_name, $img->stream('jpg', '75')->__toString());

        if (!$ret) {
            return false;
        }

        $data['filename'] = $file_name;
        $data['url_small'] = $data['url_medium'] = $data['url_full'] = ''; // we create temporary URLs on the fly
        return $data;
    }

    /**
     * @param ActivityComponent $component
     * @param $audio
     * @param $data
     * @return bool|mixed
     */
    public function uploadAudio(ActivityComponent $component, $audio, $data)
    {
        $data = json_decode($data, true);

        $s3 = Storage::disk('s3');

        $file_name = preg_replace("/[^A-Za-z0-9\_\-\.]/", '-', $audio->getClientOriginalName());
        $path_prefix = 'components/' . $component->id ;

        $ret = $s3->put($path_prefix . "-" . $file_name, file_get_contents($audio));

        if (!$ret) {
            return false;
        }

        $data['filename'] = $file_name;
        $data['url'] = ''; // we create temporary URLs on the fly
        return $data;
    }
    
    /**
     * @param ActivityComponent $component
     * @param $video
     * @param $data
     * @return bool|mixed
     */
    public function uploadVideo(ActivityComponent $component, $video, $data)
    {
        $data = json_decode($data, true);

        $s3 = Storage::disk('s3');

        $file_name = preg_replace("/[^A-Za-z0-9\_\-\.]/", '-', $video->getClientOriginalName());
        $path_prefix = 'components/' . $component->id ;

        $ret = $s3->put($path_prefix . "-" . $file_name, file_get_contents($video));

        if (!$ret) {
            return false;
        }

        $data['filename'] = $file_name;
        $data['url'] = ''; // we create temporary URLs on the fly
        return $data;
    }

    /**
     * @param ActivityComponent $component
     * @param Request $request
     * @return bool|mixed
     */
    private function updateComponentMetaData(ActivityComponent $component, Request $request)
    {
        $data = $request->data;
		
        if ($request->component_type == 'TEXT_BLOCK') {
            $value = nl2br($data['value']);
            $data['value'] = Purifier::clean($value);
            return $data;
            
        } elseif ($request->component_type == 'IMAGE') {
            if ($image = $request->file('file')) {
                return $this->uploadImage($component, $image, $data);
            }
            return json_decode($data, true);

        } elseif ($request->component_type == 'AUDIO') {
            if ($audio = $request->file('audio')) {
                return $this->uploadAudio($component, $audio, $data);
            };
            return json_decode($data, true);
        
        } elseif ($request->component_type == 'VIDEO') {
            if ($audio = $request->file('video')) {
                return $this->uploadVideo($component, $video, $data);
            };
            return json_decode($data, true);

        } elseif (in_array($request->component_type, ['TEXT_OUTPUT'])) {
            if (!isset($data['id'])) {
                $data['id'] = $component->id;
                return $data;
            }
        } elseif ($request->component_type == 'GAP_FILL') {
            return $this->getGapFillMetaData($data);
        } elseif ($request->component_type == 'TEXT_INPUT') {
            $values = json_decode($data, true);
            $values['id'] = $component->id;
            
            if (isset($values['text']) && $values['text']) {
                $value = nl2br($values['text']);
                $values['text'] = Purifier::clean($value);
            }

            if ($image = $request->file('file')) {
                $image_props = $this->uploadImage($component, $image, $data);
                $values['image'] = isset($image_props['filename']) ? $image_props['filename'] : '';
            }

            return $values;
        }
        return $data;
    }

    /**
     * Gap Fill Component
     *
     * @param $data
     * @return mixed
     */
    private function getGapFillMetaData($data)
    {

        $value = str_replace(['<p>', '</p>'], '', $data['value']);

        $data['question'] = $value;

        preg_match_all('#\[(.*?)\]#', $value, $matching_elements);

        foreach ($matching_elements[0] as $index => $match ) {
            $answers = $matching_elements[1][$index];

            $data['answers'][] = explode('/', $answers);

            // $data['question'] = str_replace($match,'__________',$data['question']);
            $pos = strpos($data['question'], $match);

            if ($pos !== false) {
                $data['question'] = substr_replace($data['question'], '__________', $pos, strlen($match));
            }
        }

        $data['sections'] = explode('__________', $data['question']);

        return $data;
    }

    /**
     * @param $component_id
     * @param CopyComponentRequest $request
     * @return mixed
     */
    public function copyComponent($component_id, CopyComponentRequest $request)
    {
        try {
            if ($component = ActivityComponent::find($component_id)) {
                $newComponent = $component->copy($request);
                return $this->respondSuccess('Component copied', new ActivityComponentResource($newComponent));
            }

            return $this->respondNotFound('Component Not Found');
        } catch (Exception $e) {
            return $this->respondInternalError($e->getMessage());
        }
    }

    /**
     * @param $component_id
     * @param CopyComponentRequest $request
     * @return mixed
     */
    public function moveComponent($component_id, CopyComponentRequest $request)
    {
        try {
            if ($component = ActivityComponent::find($component_id)) {
                $component->move($request);
                return $this->respondSuccess('Component moved', new ActivityComponentResource($component));
            }

            return $this->respondNotFound('Component Not Found');
        } catch (Exception $e) {
            return $this->respondInternalError($e->getMessage());
        }
    }
}
