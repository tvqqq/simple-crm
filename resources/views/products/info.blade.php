@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <a href="/"><b-icon icon="arrow-left" aria-hidden="true"></b-icon></a>
                        {{ $product->name }}
                    </div>

                    <div class="card-body">
                        <product-detail :product="{{ $product }}" :history="{{ $history }}"></product-detail>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
