<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use FFMpeg\FFMpeg as FFMpegLibrary;

class TestFFmpeg extends Command
{
    protected $signature = 'test:ffmpeg';

    protected $description = 'Testa a inicialização do FFmpeg';

    public function handle(): int
    {
        $ffmpeg = FFMpegLibrary::create([
            'ffmpeg.binaries' => '/usr/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/bin/ffprobe',
        ]);

        $this->info('FFmpeg inicializado com sucesso!');

        return self::SUCCESS;
    }
}