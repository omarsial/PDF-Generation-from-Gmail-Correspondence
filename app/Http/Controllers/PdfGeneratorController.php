<?php

namespace App\Http\Controllers;

use App\Services\GmailService;
use App\Services\PdfService;
use Illuminate\Http\Request;

class PdfGeneratorController extends Controller
{
    protected $gmail;
    protected $pdf;

    public function __construct(GmailService $gmail, PdfService $pdf)
    {
        $this->gmail = $gmail;
        $this->pdf = $pdf;
    }

    public function index()
    {
        return view('pdf-generator');
    }

    public function generate(Request $request)
    {
        // dd(session('gmail_token'));
        $request->validate([
            'email1' => 'required|email',
            'email2' => 'required|email'
        ]);

        if (session('gmail_token')) {
            $this->gmail->setAccessToken(session('gmail_token'));
        }

        $threads = $this->gmail->getThreadsBetween(
            $request->email1, 
            $request->email2
        );
        $html = $this->buildPdfContent($threads);

        $filename = storage_path('generated_pdf_'.time().'.pdf');
        $this->pdf->generateLargePdf($html, $filename);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    protected function buildPdfContent($threads)
    {

        $html = '<!DOCTYPE html><html><head><style>body{font-family:Arial;margin:40px}';
        $html .= '.email{margin-bottom:30px;border-bottom:1px solid #eee;padding-bottom:20px}';
        $html .= '.header{color:#666;font-size:12px;margin-bottom:10px}';
        $html .= '.content{line-height:1.6}</style></head><body>';
        
        foreach ($threads as $thread) {
            $messages = $thread->getMessages();
            
            foreach ($messages as $message) {
                $headers = $message->getPayload()->getHeaders();
                $from = $this->getHeader($headers, 'From');
                $to = $this->getHeader($headers, 'To');
                $date = $this->getHeader($headers, 'Date');
                $subject = $this->getHeader($headers, 'Subject');
                
                $body = $this->getMessageBody($message->getPayload());
                
                $html .= '<div class="email">';
                $html .= '<div class="header">';
                $html .= "<strong>From:</strong> {$from}<br>";
                $html .= "<strong>To:</strong> {$to}<br>";
                $html .= "<strong>Date:</strong> {$date}<br>";
                $html .= "<strong>Subject:</strong> {$subject}";
                $html .= '</div>';
                $html .= '<div class="content">'.nl2br(htmlspecialchars($body)).'</div>';
                $html .= '</div>';
            }
        }
        
        $html .= '</body></html>';
        return $html;
    }

    protected function getHeader($headers, $name)
    {
        foreach ($headers as $header) {
            if ($header->name === $name) {
                return $header->value;
            }
        }
        return '';
    }

    protected function getMessageBody($payload)
    {
        if ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                if ($part->getMimeType() === 'text/plain') {
                    return base64_decode(strtr($part->getBody()->getData(), '-_', '+/'));
                }
            }
        }
        return base64_decode(strtr($payload->getBody()->getData(), '-_', '+/'));
    }
}
