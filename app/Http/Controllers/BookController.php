<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Models\Review;


class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = $request->input('title');
        $filter = $request->input('filter','');
        $books = Book::when($title, fn($query,$title) =>$query->title($title)); //modeldeki scopeTitle'ı kullanıyoruz.

    $books = match($filter){
            'popular_last_month' => $books->popularLastMonth(),
            'popular_last_6months' => $books->popularLast6Months(),
            'highest_rated_last_month' => $books->highestRatedLastMonth(),
            'highest_rated_last_6months' => $books->highestRatedLast6Months(),
            'all_time_popular' => $books->popular()->withAvg('reviews','rating'),
            'all_time_highest_rated' => $books->highestRated()->withCount('reviews'),

            default =>$books->latest()->withAvg('reviews','rating')->withCount('reviews')
    };
    //$books=$books->paginate(10);
    $cacheKey = 'books:'.$filter.':'.$title;
    $books =
    //cache()->remember(
      //  $cacheKey, 3600,
        //fn()  =>
        $books->paginate(10);
    //);


        return view('books.index',compact('books')); //compact yerine ['books'=>$books] şeklinde kullanım yapılabilir.
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
{
    $cacheKey = 'book:' . $id;

    $book = cache()->remember(
        $cacheKey,
        3600,
        fn() =>
        Book::with([
            'reviews' => fn($query) => $query->latest()
        ])->withAvgRating()->withReviewsCount()->findOrFail($id)
    );

   $reviews = Review::where('book_id', $id)->latest()->paginate(5);

    return view('books.show', [
        'book' => $book,
         'reviews' => $reviews
    ]);
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
