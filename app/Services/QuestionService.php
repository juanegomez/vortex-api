<?php

namespace App\Services;

use App\Models\Questions;
use App\Models\Answers;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class QuestionService
{
    /**
     * Maneja la lógica para almacenar una nueva pregunta con respuestas.
     *
     * @param array $data
     * @return array
     */
    public function store(array $data): array
    {
        // Validar los datos de entrada
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:255',
            'answers' => 'array|max:3', // Validar que answers sea un array con un máximo de 3 elementos
            'answers.*.body' => 'required|string',
            'answers.*.is_correct' => 'required|boolean', // Validar que is_correct sea un booleano
        ]);

        // Si la validación falla, retorna un error
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Error en la validación de los datos.',
                'errors' => $validator->errors(),
                'status' => Response::HTTP_BAD_REQUEST,
            ];
        }

        // Verificar cuántas respuestas están marcadas como correctas
        $correctAnswersCount = collect($data['answers'])->where('is_correct', true)->count();

        if ($correctAnswersCount !== 1) {
            return [
                'success' => false,
                'message' => 'Debe haber exactamente una respuesta correcta.',
                'status' => Response::HTTP_BAD_REQUEST,
            ];
        }

        // Crear la pregunta
        $question = Questions::create([
            'title' => $data['title'],
            'body' => $data['body'],
        ]);

        // Verificar si la creación fue exitosa
        if (!$question) {
            return [
                'success' => false,
                'message' => 'Error al crear la pregunta.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }

        // Crear las respuestas asociadas
        if (isset($data['answers'])) {
            foreach ($data['answers'] as $answerData) {
                // Añadir el question_id al array de datos
                $answerData['question_id'] = $question->id;
                $question->answers()->create($answerData);
            }
        }

        // Retornar la respuesta exitosa
        return [
            'success' => true,
            'message' => $question,
            'status' => Response::HTTP_CREATED,
        ];
    }

    /**
     * Valida si la respuesta proporcionada es correcta para una pregunta dada.
     *
     * @param array $params
     * @return array
     */
    public function validateAnswer(array $params): array
    {
        // Validar los parámetros de entrada
        $validator = Validator::make($params, [
            'question_id' => 'required|integer|exists:questions,id',
            'answer_id' => 'required|integer|exists:answers,id'
        ]);
    
        // Si la validación falla, retorna un error
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Error en la validación de los datos.',
                'errors' => $validator->errors(),
                'is_correct' => false,
                'status' => Response::HTTP_BAD_REQUEST,
            ];
        }
    
        $question = Questions::find($params['question_id']);
        $answer = Answers::find($params['answer_id']);
    
        // Verificar si la pregunta y la respuesta existen
        if (!$question || !$answer) {
            return [
                'success' => false,
                'message' => 'Pregunta o respuesta no encontrada.',
                'is_correct' => false,
                'status' => Response::HTTP_NOT_FOUND,
            ];
        }
    
        // Verificar si la respuesta pertenece a la pregunta
        if ($answer->question_id !== $question->id) {
            return [
                'success' => false,
                'message' => 'La respuesta no pertenece a esta pregunta.',
                'is_correct' => false,
                'status' => Response::HTTP_BAD_REQUEST,
            ];
        }
    
        // Verificar si la respuesta es correcta
        if ($answer->is_correct) {
            return [
                'success' => true,
                'message' => 'La respuesta es correcta.',
                'is_correct' => true,
                'status' => Response::HTTP_OK,
            ];
        } else {
            return [
                'success' => true,
                'message' => 'La respuesta es incorrecta.',
                'is_correct' => false,
                'status' => Response::HTTP_OK,
            ];
        }
    }
}
