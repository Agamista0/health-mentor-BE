<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aleafia_questions', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('icon');
            $table->text('question_ar');
            $table->json('answer_options');  // Will store array of answer objects
            $table->timestamps();
        });

        Schema::create('aleafia_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->float('total_score');
            $table->json('category_scores');
            $table->json('answers');
            $table->timestamps();
        });

        // Insert the predefined questions with structured answers
        DB::table('aleafia_questions')->insert([
            [
                'category' => 'المزاج',
                'icon' => 'mood',
                'question_ar' => 'في الأسبوعين الماضيين، كم مرة شعرت بفقدان الاهتمام أو المتعة في الأنشطة؟',
                'answer_options' => json_encode([
                    [
                        'id' => 0,
                        'text' => 'دائمًا',
                        'score' => 0
                    ],
                    [
                        'id' => 1,
                        'text' => 'بعض الأحيان',
                        'score' => 33
                    ],
                    [
                        'id' => 2,
                        'text' => 'نادرًا',
                        'score' => 66
                    ],
                    [
                        'id' => 3,
                        'text' => 'أبدًا',
                        'score' => 100
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category' => 'النوم',
                'icon' => 'bedtime',
                'question_ar' => 'في الأسبوعين الماضيين، كم مرة شعرت بصعوبة في النوم أو الاستيقاظ؟',
                'answer_options' => json_encode([
                    [
                        'id' => 0,
                        'text' => 'دائمًا',
                        'score' => 0
                    ],
                    [
                        'id' => 1,
                        'text' => 'بعض الأحيان',
                        'score' => 33
                    ],
                    [
                        'id' => 2,
                        'text' => 'نادرًا',
                        'score' => 66
                    ],
                    [
                        'id' => 3,
                        'text' => 'أبدًا',
                        'score' => 100
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category' => 'القلق',
                'icon' => 'psychology',
                'question_ar' => 'في الأسبوعين الماضيين، كم مرة شعرت بمشاعر سلبية مثل التوتر أو الاكتئاب؟',
                'answer_options' => json_encode([
                    [
                        'id' => 0,
                        'text' => 'دائمًا',
                        'score' => 0
                    ],
                    [
                        'id' => 1,
                        'text' => 'بعض الأحيان',
                        'score' => 33
                    ],
                    [
                        'id' => 2,
                        'text' => 'نادرًا',
                        'score' => 66
                    ],
                    [
                        'id' => 3,
                        'text' => 'أبدًا',
                        'score' => 100
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category' => 'المشروبات السكرية',
                'icon' => 'local_drink',
                'question_ar' => 'ما هو متوسط عدد المشروبات الغازية التي تتناولها يوميًا؟',
                'answer_options' => json_encode([
                    [
                        'id' => 0,
                        'text' => 'لا أشرب',
                        'score' => 100
                    ],
                    [
                        'id' => 1,
                        'text' => '1-2',
                        'score' => 75
                    ],
                    [
                        'id' => 2,
                        'text' => '2-3',
                        'score' => 50
                    ],
                    [
                        'id' => 3,
                        'text' => 'أكثر من 4',
                        'score' => 0
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category' => 'النظام الغذائي',
                'icon' => 'restaurant',
                'question_ar' => 'في الأسبوعين الماضيين، كم مرة تناولت طعامًا غير صحي مثل الوجبات السريعة؟',
                'answer_options' => json_encode([
                    [
                        'id' => 0,
                        'text' => 'أبدًا',
                        'score' => 100
                    ],
                    [
                        'id' => 1,
                        'text' => 'نادرًا',
                        'score' => 75
                    ],
                    [
                        'id' => 2,
                        'text' => 'بعض الأحيان',
                        'score' => 50
                    ],
                    [
                        'id' => 3,
                        'text' => 'دائمًا',
                        'score' => 0
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category' => 'النشاط البدني',
                'icon' => 'fitness_center',
                'question_ar' => 'في الأسبوعين الماضيين، كم مرة مارست نشاطًا بدنيًا؟',
                'answer_options' => json_encode([
                    [
                        'id' => 0,
                        'text' => 'أبدًا',
                        'score' => 0
                    ],
                    [
                        'id' => 1,
                        'text' => 'نادرًا',
                        'score' => 33
                    ],
                    [
                        'id' => 2,
                        'text' => 'بعض الأحيان',
                        'score' => 66
                    ],
                    [
                        'id' => 3,
                        'text' => 'دائمًا',
                        'score' => 100
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aleafia_assessments');
        Schema::dropIfExists('aleafia_questions');
    }
};
