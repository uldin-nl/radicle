{{--
    Template Name: Main Template
--}}

@extends('layouts.app')

@section('content')
    @while (have_posts())
        @php the_post() @endphp
        @if (have_rows('content'))
            @while (have_rows('content'))
                @php the_row() @endphp
                @include('partials.' . get_row_layout())
            @endwhile
        @endif
    @endwhile
@endsection
