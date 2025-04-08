<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;

class SupabaseTestController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index()
    {
        $userId = session('supabase_user_id'); // Use Supabase UUID
        \Log::info("Fetching data for user_id: {$userId}");

        $data = $this->supabase->getTableData('listings');

        return response()->json($data);
    }
}
