@extends('base::layouts.mt-main')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-8">
        <h1 class="fs-2 fw-bolder">Hot Deals</h1>
        <a href="{{ route('hot_deals.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Hot Deal
        </a>
    </div>

    <div class="card card-custom card-flush">
        <div class="card-body">
            <table class="table table-row-bordered table-row-gray-300 gy-5 gs-7">
                <thead>
                    <tr class="text-gray-600 fw-bolder fs-6">
                        <th class="min-w-50px">#</th>
                        <th class="min-w-100px">Discount (%)</th>
                        <th class="min-w-150px">Start Date</th>
                        <th class="min-w-150px">End Date</th>
                        <th class="min-w-300px">Products</th>
                        <th class="min-w-150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hotDeals as $deal)
                        <tr>
                            <td>{{ $deal->id }}</td>
                            <td>{{ $deal->discount }}</td>
                            <td>{{ $deal->start_date }}</td>
                            <td>{{ $deal->end_date }}</td>
                            <td>
                              <div class="hotdeal-products" id="hotdeal-products-{{ $deal->id }}">
                                  @foreach($deal->products->take(3) as $product)
                                      <div class="d-flex align-items-center mb-2">
                                          @if($product->images->first())
                                              <div class="symbol symbol-50px me-3">
                                                  <img src="{{ asset($product->images->first()->url) }}" alt="{{ $product->name }}" class="rounded">
                                              </div>
                                          @endif
                                          <span class="text-gray-800">{{ $product->name }}</span>
                                      </div>
                                  @endforeach
                          
                                  @if($deal->products->count() > 3)
                                      <div class="collapse" id="more-products-{{ $deal->id }}">
                                          @foreach($deal->products->slice(3) as $product)
                                              <div class="d-flex align-items-center mb-2">
                                                  @if($product->images->first())
                                                      <div class="symbol symbol-50px me-3">
                                                          <img src="{{ asset($product->images->first()->url) }}" alt="{{ $product->name }}" class="rounded">
                                                      </div>
                                                  @endif
                                                  <span class="text-gray-800">{{ $product->name }}</span>
                                              </div>
                                          @endforeach
                                      </div>
                                      <button type="button" class="btn btn-link p-0 mt-1" data-bs-toggle="collapse" data-bs-target="#more-products-{{ $deal->id }}">
                                          Show All
                                      </button>
                                  @endif
                              </div>
                          </td>
                          
                            <td>
                              <a href="{{ route('hot_deals.show', $deal->id) }}" class="btn btn-sm btn-light btn-active-light-info me-2">
                                 <i class="fas fa-eye"></i> View
                             </a>                             
                                <a href="{{ route('edit', $deal->id) }}" class="btn btn-sm btn-light btn-active-light-primary me-2">
                                    <i class="fas fa-edit fs-6"></i> Edit
                                </a>
                                <form action="{{ route('delete', $deal->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light btn-active-light-danger delete-banner">
                                       <i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection