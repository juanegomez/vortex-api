<?php

namespace App\Exports;

use App\Models\Questions;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class QuestionsExport implements FromCollection, WithHeadings, WithColumnFormatting
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Obtener todas las preguntas
        return Questions::all()->map(function ($question) {
            return [
                $question->id,
                $question->title,
                $question->body,
                $question->created_at->format('d-m-Y'),
            ];
        });
    }

    /**
     * Define las cabeceras de las columnas.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Título',
            'Pregunta',
            'Fecha de creación',
            ];
    }

    /**
     * Define el formato de las columnas.
     *
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY, // Fecha de Creación
        ];
    }
}