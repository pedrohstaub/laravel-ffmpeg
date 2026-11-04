<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\Log;
use Throwable;

class VideoController extends Controller
{
    public function download(Request $request, $frame)
    {
        try {
            //validate
            if (!is_numeric($frame) || $frame < 0) {
                return response()->json([
                    'error' => 'Frame value unsuported.'
                ], 422);
            }

            //create from bin
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
                'ffprobe.binaries' => '/usr/bin/ffprobe',
            ]);

            $videoPath = storage_path('app/public/assets/example.mp4');

            //validate video path
            if (!file_exists($videoPath)) {
                return response()->json([
                    'error' => 'Video file not found.'
                ], 404);
            }

            //get video info
            $ffprobe  = $ffmpeg->getFFProbe();
            $duration = (float) $ffprobe->format($videoPath)->get('duration');

            $stream = $ffprobe->streams($videoPath)->videos()->first();
            [$num, $den] = array_pad(explode('/', $stream->get('r_frame_rate')), 2, 1);
            $fps = $den > 0 ? $num / $den : 0;

            $totalFrames = (int) floor($duration * $fps);

            //check if frame requested is in range
            $frameNumber = (int) $frame;
            if ($frameNumber >= $totalFrames) {
                return response()->json([
                    'error'        => 'The requested frame is out of the video range .',
                    'total_frames' => $totalFrames,
                ], 422);
            }
            $seconds = $frameNumber / $fps;

            // Extract
            $frameDirectory = storage_path('app/public/frames');

            if (!is_dir($frameDirectory)) {
                mkdir($frameDirectory, 0775, true);
            }

            $name      = 'frame-' . time() . '.png';
            $framePath = $frameDirectory . '/' . $name;

            $ffmpeg->open($videoPath)
                ->frame(TimeCode::fromSeconds($seconds))
                ->save($framePath);

            if (!file_exists($framePath)) {
                throw new \RuntimeException('FFmpeg could not generate PNG file.');
            }

            return response()->download($framePath, $name)->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            Log::error('Error on extracting frame', [
                'message'  => $e->getMessage(),
                'previous' => $e->getPrevious()?->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}