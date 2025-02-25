<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    // Retrieve articles with filters (GET API)
    public function index(Request $request)
    {
        $query = Article::query();

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('author')) {
            $query->where('author', $request->author);
        }
        
        if ($request->has('start_date')) {
            $query->whereDate('published_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('published_at', '<=', $request->end_date);
        }
        
        return response()->json($query->paginate(10));
    }

    public function search(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'search' => 'nullable|string',
            'sources' => 'nullable|array',
            'sources.*' => 'string|max:255',
            'authors' => 'nullable|array',
            'authors.*' => 'string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Article::query();

        if (!empty($validated['search'])) {
            $query->where(function ($q) use ($validated) {
                $q->where('title', 'like', '%' . $validated['search'] . '%')
                    ->orWhere('description', 'like', '%' . $validated['search'] . '%');
            });
        }

        if (!empty($validated['sources'])) {
            $query->whereIn('source', $validated['sources']);
        }

        if (!empty($validated['authors'])) {
            $query->whereIn('author', $validated['authors']);
        }
        
        if (!empty($validated['start_date'])) {
            $query->whereDate('published_at', '>=', $validated['start_date']);
        }
        
        if (!empty($validated['end_date'])) {
            $query->whereDate('published_at', '<=', $validated['end_date']);
        }

        return response()->json($query->paginate(10));
    }


}

