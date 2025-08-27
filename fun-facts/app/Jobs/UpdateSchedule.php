<?php

namespace App\Jobs;

use App\Models\Manager;
use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class UpdateSchedule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     * This job requires a CSV file named "schedule.csv" to be present in the storage folder.
     * It's the same file that's created for the draft project.
     */
    public function handle(): void
    {
        $success = true;
        $message = "";
        $thisYear = date('Y');

        // Get the file from storage folder
        $file = Storage::get('schedule.csv');

        if (!$file) {
            $success = false;
            echo "Failed to retrieve schedule file.";
            return;
        }

        $array = $this->getCsvFileContents();

        Schedule::truncate();

        $row = 1;
        foreach ($array as $data) {
            $week = (int) str_replace("Week ", "", $data[0]);
            
            if ($row > 1) {
                $columnStart = 1;
                for ($x = $columnStart; $x < 10; $x+=2) {
                    $man1id = Manager::where('name', $data[$x])->first()->id;
                    $man2id = Manager::where('name', $data[$x+1])->first()->id;

                    Schedule::create([
                        'manager1_id' => $man1id,
                        'manager2_id' => $man2id,
                        'year' => $thisYear,
                        'week' => $week
                    ]);
                }
            }
            $row++;
        }

        echo 'Schedule updated successfully.';
    }

    public function getCsvFileContents()
    {
        $csvData = Storage::get("schedule.csv");
        $lines = explode(PHP_EOL, $csvData);
        $array = [];
        foreach ($lines as $line) {
            $array[] = str_getcsv($line, ",", '"', '#');
        }

        return $array;
    }
}