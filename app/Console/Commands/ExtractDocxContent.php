<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExtractDocxContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extract:docx-content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
   public function handle()
{
    $docPath = storage_path('app/sample_content.docx');
    $phpWord = \PhpOffice\PhpWord\IOFactory::load($docPath);
    $text = '';

    foreach ($phpWord->getSections() as $section) {
        foreach ($section->getElements() as $element) {
            if (method_exists($element, 'getText')) {
                $text .= $element->getText() . "\n\n";
            }
        }
    }

    file_put_contents(storage_path('app/email_body.txt'), $text);
    $this->info('DOCX content extracted.');
}

}
