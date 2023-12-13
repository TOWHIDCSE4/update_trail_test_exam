<?php

namespace App\Console\Commands;

use App\Models\LibraryQuestion;
use App\Models\LibraryTest;
use App\Models\Section;
use App\Models\SectionQuestion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class recoverQuestionTopic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recover:question_section';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recover question section';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('start recover question section >>');
        try {
            $listTopic = LibraryTest::query()->whereNull('section_ids')->get();
            foreach ($listTopic as $index => $topic){
                try {
                    DB::beginTransaction();
                    $listQuestion = LibraryQuestion::query()->where('library_test_id', $topic->id)->get();
                    if($listQuestion && count($listQuestion) > 0) {
                        $sectionId = Section::query()->insertGetId([
                            'section_name' => 'section ' . $index+1,
                            'creator_id' => $topic->creator_id
                        ]);
                        foreach ($listQuestion as $question) {
                            SectionQuestion::query()->create([
                                'section_id' => $sectionId,
                                'question_id' => $question->id,
                                'question_order' => $question->order,
                                'creator_id' => $question->creator_id
                            ]);
                        }
                        $topic->update([
                            'section_ids' => $sectionId
                        ]);
                        DB::commit();
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    Log::error($e);
                    Log::info('recover err topic_id : '.$topic->id );
                    continue;

                }
            }
            Log::info('end recover question section <<');
        } catch (\Exception $e) {

            Log::error($e);
            Log::info('job:recoverQuestionSection error');

        }
    }
}
