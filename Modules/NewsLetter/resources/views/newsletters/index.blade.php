@extends('base::layouts.mt-main')

@section('content')
<!--begin::Card-->
<div class="card">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <!--begin::Card title-->
        <div class="card-title">
            <h1 class="mb-0">Newsletter List</h1>
        </div>
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            @if(auth()->user()->is_super_admin || auth()->user()->can('create_newsletter_subscriber'))
            <a href="{{ route('newsletters.create') }}" class="btn btn-primary">
                <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                <span class="svg-icon svg-icon-2">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1" transform="rotate(-90 11.364 20.364)" fill="currentColor" />
                        <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="currentColor" />
                    </svg>
                </span>
                <!--end::Svg Icon-->
                Add New Subscriber
            </a>
            @endif

        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body py-4">
        @if ($newsletters->count() > 0)
        <!--begin::Table-->
        <table class="table align-middle table-row-dashed fs-6 gy-5">
            <!--begin::Table head-->
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">Email</th>
                    <th class="min-w-125px">Status</th>
                    <th class="min-w-125px">Subscribed At</th>
                    <th class="text-end min-w-100px">Actions</th>
                </tr>
            </thead>
            <!--end::Table head-->
            <!--begin::Table body-->
            <tbody class="text-gray-600 fw-semibold">
                @foreach($newsletters as $newsletter)
                <tr>
                    <td>{{ $newsletter->email }}</td>
                    <td>
                        @if($newsletter->status === 'subscribed')
                        <span class="badge badge-light-success fw-bolder me-auto">Subscribed</span>
                        @elseif($newsletter->status === 'unsubscribed')
                        <span class="badge badge-light-danger fw-bolder me-auto">Unsubscribed</span>
                        @else
                        <span class="badge badge-light-secondary fw-bolder me-auto">{{ $newsletter->status }}</span>
                        @endif
                    </td>

                    <td>{{ $newsletter->subscribed_at }}</td>

                    <td class="text-end">
                        @if(auth()->user()->is_super_admin || auth()->user()->can('edit_newsletter_subscriber'))

                        <a href="{{ route('newsletters.edit', $newsletter->id) }}" class="btn btn-sm btn-light btn-active-light-primary"> <i class="fas fa-edit"></i> Edit</a>
                        @endif

                    @if(auth()->user()->is_super_admin || auth()->user()->can('delete_newsletter_subscriber'))

                        <form action="{{ route('newsletters.destroy', $newsletter->id) }}" method="POST" class="d-inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-light btn-active-light-danger sweet-delete">
                                <i class="fas fa-trash"></i> {{ __('Delete') }}
                            </button>
                        </form>
                            @endif

                        {{-- <form action="{{ route('newsletters.sendEmail', $newsletter->id) }}" method="POST" class="d-inline-block">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success btn-active-success" onclick="return confirm('Send email to this address?')">Send Email</button>
                        </form> --}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <!--end::Table body-->
        </table>
        <!--end::Table-->
        @else
        <p>No newsletter subscribers found.</p>
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.sweet-delete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = button.closest('form');
                        form.submit();

                        Swal.fire(
                            'Deleted!',
                            'The subscriber has been deleted.',
                            'success'
                        )
                    }
                })
            });
        });
    });
</script>
