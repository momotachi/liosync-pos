@extends('layouts.company')

@section('title', 'Edit Company - ' . $company->name)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 400px;
        width: 100%;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        z-index: 1;
    }
    .dark #map {
        border-color: #374151;
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('company.branches.index', $company) }}" class="hover:text-primary transition-colors">Branches</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span>Edit Company</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Company Profile</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update your company information</p>
</div>

<!-- Edit Form -->
<div class="max-w-3xl">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <form action="{{ route('company.update', $company) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Company Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">business</span>
                        Company Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Name *</label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name', $company->name) }}"
                                   required
                                   placeholder="Enter company name"
                                   class="w-full border border-border-light dark:border-border-dark rounded-lg px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Code *</label>
                            <input type="text"
                                   name="code"
                                   id="code"
                                   value="{{ old('code', $company->code) }}"
                                   required
                                   placeholder="e.g. JUICE001"
                                   class="w-full border border-border-light dark:border-border-dark rounded-lg px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('code')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   value="{{ old('email', $company->email) }}"
                                   placeholder="company@example.com"
                                   class="w-full border border-border-light dark:border-border-dark rounded-lg px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                            <input type="text"
                                   name="phone"
                                   id="phone"
                                   value="{{ old('phone', $company->phone) }}"
                                   placeholder="+62 xxx xxxx xxxx"
                                   class="w-full border border-border-light dark:border-border-dark rounded-lg px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('phone')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tax_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax ID</label>
                            <input type="text"
                                   name="tax_id"
                                   id="tax_id"
                                   value="{{ old('tax_id', $company->tax_id) }}"
                                   placeholder="NPWP / Tax ID"
                                   class="w-full border border-border-light dark:border-border-dark rounded-lg px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('tax_id')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <!-- Company Type (Read-only for Company Admin) -->
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Company Type
                            </label>
                            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-border-light dark:border-border-dark">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg {{ $company->type === 'resto' ? 'bg-orange-100 dark:bg-orange-900/30' : 'bg-blue-100 dark:bg-blue-900/30' }} flex items-center justify-center">
                                            <span class="material-symbols-outlined {{ $company->type === 'resto' ? 'text-orange-600 dark:text-orange-400' : 'text-blue-600 dark:text-blue-400' }}">
                                                {{ $company->type === 'resto' ? 'restaurant' : 'shopping_bag' }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">
                                                {{ $company->type === 'resto' ? 'Resto (Restaurant)' : 'Toko (Shop)' }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $company->type === 'resto' ? 'Order then pay flow' : 'Direct payment flow' }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                        Read-only
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">info</span>
                                    Contact Superadmin to change company type
                                </p>
                                <input type="hidden" name="type" value="{{ $company->type }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <textarea name="address"
                              id="address"
                              rows="3"
                              placeholder="Full company address"
                              class="w-full border border-border-light dark:border-border-dark rounded-lg px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none">{{ old('address', $company->address) }}</textarea>
                    @error('address')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location Map -->
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">location_on</span>
                        Company Location
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Click on the map to set your company location</p>

                    <!-- Map Container -->
                    <div id="map" class="mb-4"></div>

                    <!-- Latitude & Longitude Display -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Latitude</label>
                            <input type="text"
                                   id="latitude"
                                   value="{{ old('latitude', $company->latitude) }}"
                                   readonly
                                   class="w-full border border-border-light dark:border-border-dark rounded-lg px-4 py-2.5 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                            <input type="hidden" name="latitude" id="latitude_input" value="{{ old('latitude', $company->latitude) }}">
                        </div>
                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Longitude</label>
                            <input type="text"
                                   id="longitude"
                                   value="{{ old('longitude', $company->longitude) }}"
                                   readonly
                                   class="w-full border border-border-light dark:border-border-dark rounded-lg px-4 py-2.5 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                            <input type="hidden" name="longitude" id="longitude_input" value="{{ old('longitude', $company->longitude) }}">
                        </div>
                    </div>
                    @error('latitude')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @error('longitude')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Read-only Fields -->
                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-border-light dark:border-border-dark">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">info</span>
                        Company Status Information
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Status:</span>
                            <span class="ml-2 {{ $company->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $company->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Business Type:</span>
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $company->type === 'resto' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' }}">
                                <span class="material-symbols-outlined text-xs mr-1">{{ $company->type === 'resto' ? 'restaurant' : 'shopping_bag' }}</span>
                                {{ $company->type === 'resto' ? 'Resto' : 'Toko' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Structure:</span>
                            <span class="ml-2 text-gray-700 dark:text-gray-300">
                                {{ $company->has_branches ? 'Multi-Branch' : 'Single' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex justify-end gap-3 pt-6 border-t border-border-light dark:border-border-dark">
                <a href="{{ route('company.branches.index', $company) }}"
                   class="inline-flex items-center px-4 py-2.5 border border-border-light dark:border-border-dark text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <span class="material-symbols-outlined text-sm mr-1">cancel</span>
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition-colors">
                    <span class="material-symbols-outlined text-sm mr-1">save</span>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Default to Jakarta if no location set
        const defaultLat = {{ $company->latitude ?? '-6.2088' }};
        const defaultLng = {{ $company->longitude ?? '106.8456' }};
        const zoomLevel = {{ $company->latitude ? '15' : '11' }};

        // Initialize map
        const map = L.map('map').setView([defaultLat, defaultLng], zoomLevel);

        // Add tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // Add marker
        let marker = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(map);

        // Reverse geocoding function - get address from coordinates
        async function fetchAddress(lat, lng) {
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                    headers: {
                        'Accept-Language': 'id-ID,en-US'
                    }
                });
                const data = await response.json();
                if (data && data.display_name) {
                    // Format the address
                    let address = data.display_name;
                    // Try to get a more concise address
                    if (data.address) {
                        const parts = [];
                        if (data.address.road) parts.push(data.address.road);
                        if (data.address.suburb) parts.push(data.address.suburb);
                        if (data.address.city || data.address.town || data.address.village) parts.push(data.address.city || data.address.town || data.address.village);
                        if (data.address.state) parts.push(data.address.state);
                        if (data.address.country) parts.push(data.address.country);
                        if (parts.length > 0) {
                            address = parts.join(', ');
                        }
                    }
                    // Update address field
                    const addressTextarea = document.getElementById('address');
                    if (addressTextarea) {
                        addressTextarea.value = address;
                        // Trigger input event for any listeners
                        addressTextarea.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            } catch (error) {
                console.log('Could not fetch address:', error);
            }
        }

        // Function to update location and fetch address
        function updateLocation(lat, lng) {
            marker.setLatLng([lat, lng]);
            document.getElementById('latitude').value = lat.toFixed(7);
            document.getElementById('longitude').value = lng.toFixed(7);
            document.getElementById('latitude_input').value = lat.toFixed(7);
            document.getElementById('longitude_input').value = lng.toFixed(7);
            // Fetch address for the new location
            fetchAddress(lat, lng);
        }

        // Update input fields when marker is dragged
        marker.on('dragend', function(e) {
            const position = marker.getLatLng();
            updateLocation(position.lat, position.lng);
        });

        // Update marker position when map is clicked
        map.on('click', function(e) {
            const { lat, lng } = e.latlng;
            updateLocation(lat, lng);
        });
    });
</script>
@endpush
@endsection
