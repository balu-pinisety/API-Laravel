<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use JWTAuth;
use App\Models\Note;

class NoteController extends Controller
{

    /**
     * Creates Note by user authentication
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNote(Request $request)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $note = new Note;

        $note->title = $request->get('title');
        $note->description = $request->get('description');

        if($currentUser->notes()->save($note))
        {
            return response()->json([
                'message' => 'Note created Sucessfully'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Could not Create Note'
            ], 200);
        }
    }

    /**
     * Diaplay particular note by id
     *
     */
    public function showNote($id)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $note = $currentUser->notes()->find($id);

        if(!$note)
            throw new NotFoundHttpException;

        return $note;
    }

    /**
     * Update Note by user particular id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNote(Request $request, $id)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        $note = $currentUser->notes()->find($id);
        if(!$note)
            throw new NotFoundHttpException;

        $note->fill($request->all());

        if($note->save())
            //return $this->response->noContent();
            return response()->json([
                'message' => 'Note updated Sucessfully'
                //'user' => $user
            ], 201);
        else
            return response()->json([
                'message' => 'Could not Update Note'
            ], 200);
    }

    /**
     * Update Note by user particular id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();


        $note = $currentUser->notes()->find($id);

        if(!$note)
            throw new NotFoundHttpException;

        if($note->delete())
            //return $this->response->noContent();
            return response()->json([
                'message' => 'Note deleted Sucessfully'
                //'user' => $user
            ], 201);
        else
            return $this->response->error('could_not_delete_note', 500);
    }

}
