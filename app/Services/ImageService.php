<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class ImageService {

    public string $ticketTemplate = "ticket.png";
    public int $ticketId = 0;

    public function generateTicketImage()
    {
        try {
            $image = Image::make(public_path('ticket.png'));
            $image->text(str_pad($this->ticketId, 6, 0, STR_PAD_LEFT), 365, 320, function ($font) {
                $font->file(public_path('Montserrat-VariableFont_wght.ttf'));
                $font->size(40);
                $font->color('#000');
                $font->align('center');
                $font->valign('top');
            });
            $image->save(storage_path('app/public/uploads/'.$this->ticketId.'.jpeg'));
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            Log::debug($e->getTraceAsString());
        }
    }
}
