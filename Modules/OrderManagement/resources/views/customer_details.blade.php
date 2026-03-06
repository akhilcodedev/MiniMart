@extends('base::layouts.mt-main')

@section('content')
  <div class="d-flex flex-column flex-root">
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
      <div class="container-xxl" id="kt_content_container">
        <div class="row g-5">
          <!-- Left Sidebar -->
          <div class="col-md-4 col-lg-3">
            <div class="card card-flush mb-5 shadow-sm">
              <div class="card-body text-center pt-9 pb-0 position-relative">
                <!-- Status Ribbon -->
                <div class="position-absolute top-0 end-0 m-3">
                  @if($customer->is_active)
                    <span class="badge badge-lg badge-light-success fw-bold px-3 py-2">
                      <i class="bi bi-check-circle-fill me-2"></i>Active
                    </span>
                  @else
                    <span class="badge badge-lg badge-light-danger fw-bold px-3 py-2">
                      <i class="bi bi-x-circle-fill me-2"></i>Inactive
                    </span>
                  @endif
                </div>

                <!-- Profile Section -->
                <div class="symbol symbol-100px symbol-circle mb-4 profile-image-container bg-light-primary position-relative mx-auto">
                  <img src="https://placehold.co/100x100" alt="Profile Image"
                       class="profile-image object-cover rounded-circle border border-3 border-white shadow-sm">
                </div>


                <!-- Customer Info -->
                <h3 class="fw-bold mb-2 text-gray-800">{{ $customer->first_name }} {{ $customer->last_name }}</h3>
                <div class="text-muted fw-semibold mb-5">{{ $customer->email }}</div>

                <!-- Details Card -->
                <div class="card card-bordered mt-4">
                  <div class="card-header bg-light-primary py-4">
                    <h4 class="card-title fw-bold text-primary mb-0">
                      <i class="bi bi-person-lines-fill me-2"></i>Customer Details
                    </h4>
                  </div>
                  <div class="card-body p-4">
                    <ul class="list-unstyled details-list mb-0">
                      <li class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-gray-600"><i class="bi bi-person me-2"></i>First Name</span>
                        <span class="text-gray-800 fw-semibold">{{ $customer->first_name }}</span>
                      </li>
                      <li class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-gray-600"><i class="bi bi-person-plus me-2"></i>Last Name</span>
                        <span class="text-gray-800 fw-semibold">{{ $customer->last_name }}</span>
                      </li>
                      <li class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-gray-600"><i class="bi bi-envelope me-1"></i></span>
                        <span class="text-gray-800 fw-semibold">{{ $customer->email }}</span>
                      </li>
                      <li class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-gray-600"><i class="bi bi-telephone me-2"></i>Phone</span>
                        <span class="text-gray-800 fw-semibold">{{ $customer->phone ?? 'N/A' }}</span>
                      </li>
                    </ul>
                  </div>
                </div>
                </div>
            </div>
        </div>
            <!-- Main Content Area -->
            <div class="col-md-8 col-lg-9">
              <!-- Tabs Navigation -->
              <div class="d-flex align-items-center mb-5">
                <h2 class="fw-bold text-gray-800 me-4 mb-0">Customer Order Overview</h2>
                <ul class="nav nav-tabs nav-line-tabs border-0 fs-6 fw-semibold">
                  <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#overview">
                      <i class="bi bi-house-door me-2"></i>Overview
                    </a>
                  </li>
                </ul>
              </div>

              <!-- Address Card -->
              <div class="card card-flush shadow-sm">
                <div class="card-header bg-light-warning py-4">
                  <h4 class="card-title fw-bold text-gray-800 mb-0">
                    <i class="bi bi-geo-alt-fill me-2"></i>Delivery Addresses
                  </h4>
                </div>
                <div class="card-body p-4">
                  <div class="table-responsive">
                    <table class="table table-hover align-middle table-row-dashed fs-6">
                      <thead class="bg-light">
                        <tr class="fw-bold text-muted">
                          <th class="ps-4">Address</th>
                          <th>City</th>
                          <th>State</th>
                          <th>Postal Code</th>
                          <th>Country</th>
                          <th>Type</th>
                          <th class="text-center">Default</th>
                        </tr>
                      </thead>
                      <tbody class="text-gray-600">
                        @forelse($customer->addresses as $address)
                          <tr class="animated-row position-relative">
                            <td class="ps-4">
                              <div class="d-flex align-items-center">
                                <i class="bi bi-geo fs-4 text-primary me-3"></i>
                                <div>
                                  <div class="fw-semibold">{{ $address->address_line1 }}</div>
                                  <div class="text-muted">{{ $address->address_line2 }}</div>
                                </div>
                              </div>
                            </td>
                            <td>{{ $address->city }}</td>
                            <td>{{ $address->state }}</td>
                            <td>{{ $address->postal_code }}</td>
                            <td>{{ $address->country }}</td>
                            <td>
                              <span class="badge badge-light-{{ $address->type === 'billing' ? 'danger' : 'success' }}">
                                {{ ucfirst($address->type) }}
                              </span>
                            </td>
                            <td class="text-center">
                              @if($address->is_default)
                                <i class="bi bi-check2-circle fs-3 text-success"></i>
                              @else
                                <span class="text-muted">-</span>
                              @endif
                            </td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                              <i class="bi bi-info-circle fs-2"></i>
                              <div class="mt-2">No addresses found</div>
                            </td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

@endsection
@section('custom-js-section')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Profile image hover effect
      const profileContainer = document.querySelector('.profile-image-container');
      if(profileContainer) {
        profileContainer.addEventListener('mouseenter', () => {
          profileContainer.style.transform = 'scale(1.05) rotate(2deg)';
        });
        profileContainer.addEventListener('mouseleave', () => {
          profileContainer.style.transform = 'scale(1) rotate(0)';
        });
      }

      // Animated row entrance
      document.querySelectorAll('.animated-row').forEach((row, index) => {
        setTimeout(() => {
          row.style.opacity = '1';
          row.style.transform = 'translateY(0)';
        }, 100 * index);
      });
    });
  </script>

  <style>
    .profile-image-container {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .profile-image {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .details-list li {
      padding: 0.75rem 1rem;
      border-radius: 0.5rem;
      transition: all 0.2s ease;
    }

    .details-list li:hover {
      background-color: #f8f9fa;
      transform: translateX(5px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .animated-row {
      opacity: 0;
      transform: translateY(10px);
      transition: opacity 0.4s ease, transform 0.4s ease;
    }

    .table-hover tbody tr:hover {
      background-color: #f9fafb;
      box-shadow: inset 4px 0 0 0 #3699FF;
    }

    .card-header {
      border-bottom: 2px solid rgba(0, 0, 0, 0.05);
    }

    .btn-hover-scale {
      transition: transform 0.2s ease;
    }

    .btn-hover-scale:hover {
      transform: translateY(-1px);
    }
  </style>
@endsection