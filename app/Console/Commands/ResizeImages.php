<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ResizeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:resize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resize all S3 bucket images';

    private $s3;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->s3 = Storage::disk('s3');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Listing all files...');
        $files = array_filter($this->s3->files('components/'), function ($item) {
            return strpos($item, '-full-');
        });
        $this->info('Total ' . count($files) . ' file(s) found for processing.');

        $bar = $this->output->createProgressBar(count($files));

        foreach ($files as $file) {
            $contents = $this->s3->get($file);
            $img = Image::make($contents);
            $img->getCore()->setInterlaceScheme(\Imagick::INTERLACE_PLANE);

            //$this->info('Orignal size: ' . $img->width() . 'px X ' . $img->height() . 'px');
            $img->resize(1024, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $filename = str_replace("-full-", "-medium-", $file);
            $this->s3->put($filename, $img->stream('jpg', '75')->__toString());

            $img->resize(768, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $filename = str_replace("-full-", "-small-", $file);
            $this->s3->put($filename, $img->stream('jpg', '75')->__toString());

            $bar->advance();
        }

        $this->info('');
        $this->info('Process completed!');
    }
}
