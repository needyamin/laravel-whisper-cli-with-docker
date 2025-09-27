<?php

namespace Database\Seeders;

use App\Models\EnglishParagraph;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnglishParagraphSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paragraphs = [
            [
                'content' => 'The quick brown fox jumps over the lazy dog. This sentence contains every letter of the alphabet and is commonly used for typing practice.',
                'difficulty_level' => 'easy',
                'word_count' => 20,
                'keywords' => ['quick', 'brown', 'fox', 'jumps', 'lazy', 'dog', 'alphabet', 'typing', 'practice']
            ],
            [
                'content' => 'Education is the most powerful weapon which you can use to change the world. Knowledge opens doors to opportunities and helps us understand different perspectives.',
                'difficulty_level' => 'medium',
                'word_count' => 25,
                'keywords' => ['education', 'powerful', 'weapon', 'change', 'world', 'knowledge', 'opportunities', 'perspectives']
            ],
            [
                'content' => 'Technology has revolutionized the way we communicate, work, and live our daily lives. From smartphones to artificial intelligence, innovation continues to shape our future.',
                'difficulty_level' => 'medium',
                'word_count' => 28,
                'keywords' => ['technology', 'revolutionized', 'communicate', 'smartphones', 'artificial', 'intelligence', 'innovation', 'future']
            ],
            [
                'content' => 'The environment faces numerous challenges including climate change, deforestation, and pollution. Sustainable practices and renewable energy sources are essential for preserving our planet.',
                'difficulty_level' => 'hard',
                'word_count' => 30,
                'keywords' => ['environment', 'challenges', 'climate', 'change', 'deforestation', 'pollution', 'sustainable', 'renewable', 'energy', 'preserving']
            ],
            [
                'content' => 'Reading books enhances vocabulary, improves concentration, and expands imagination. Literature provides insights into human nature and different cultures around the world.',
                'difficulty_level' => 'medium',
                'word_count' => 26,
                'keywords' => ['reading', 'books', 'vocabulary', 'concentration', 'imagination', 'literature', 'insights', 'human', 'nature', 'cultures']
            ],
            [
                'content' => 'Physical exercise is crucial for maintaining good health and mental well-being. Regular workouts strengthen muscles, improve cardiovascular health, and reduce stress levels.',
                'difficulty_level' => 'medium',
                'word_count' => 25,
                'keywords' => ['physical', 'exercise', 'crucial', 'maintaining', 'health', 'mental', 'well-being', 'workouts', 'muscles', 'cardiovascular']
            ],
            [
                'content' => 'Cooking is both an art and a science that brings people together. Learning to prepare meals develops creativity, patience, and appreciation for different flavors and ingredients.',
                'difficulty_level' => 'medium',
                'word_count' => 27,
                'keywords' => ['cooking', 'art', 'science', 'people', 'learning', 'prepare', 'meals', 'creativity', 'patience', 'appreciation', 'flavors']
            ],
            [
                'content' => 'Travel broadens the mind and exposes us to new experiences, cultures, and ways of thinking. Exploring different countries helps us understand global diversity and develop empathy.',
                'difficulty_level' => 'hard',
                'word_count' => 28,
                'keywords' => ['travel', 'broadens', 'mind', 'exposes', 'experiences', 'cultures', 'thinking', 'exploring', 'countries', 'global', 'diversity', 'empathy']
            ],
            [
                'content' => 'Music has the power to evoke emotions, tell stories, and bring people together across cultures. Learning to play an instrument improves coordination and cognitive abilities.',
                'difficulty_level' => 'medium',
                'word_count' => 26,
                'keywords' => ['music', 'power', 'evoke', 'emotions', 'stories', 'cultures', 'learning', 'instrument', 'coordination', 'cognitive']
            ],
            [
                'content' => 'Friendship is one of life\'s greatest treasures that provides support, joy, and companionship. True friends accept us for who we are and help us grow as individuals.',
                'difficulty_level' => 'easy',
                'word_count' => 24,
                'keywords' => ['friendship', 'life', 'treasures', 'support', 'joy', 'companionship', 'true', 'friends', 'accept', 'individuals']
            ]
        ];

        foreach ($paragraphs as $paragraph) {
            EnglishParagraph::create($paragraph);
        }
    }
}
