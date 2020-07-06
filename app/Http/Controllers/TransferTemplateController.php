<?php


namespace App\Http\Controllers;


use App\Models\TransferTemplate;

class TransferTemplateController extends Controller
{
    public function get(string $id)
    {
        return response()->json(TransferTemplate::query()->findOrFail($id));
    }
}
