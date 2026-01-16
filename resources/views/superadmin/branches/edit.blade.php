@extends('layouts.superadmin')

@section('title', 'Edit Branch - ' . $branch->name)

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
        <a href="/superadmin/companies" class="hover:text-primary transition-colors">Companies</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <a href="{{ route('company.branches.index', $company) }}" class="hover:text-primary transition-colors">{{ $company->name }}</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span>Edit Branch</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Branch</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update branch information for {{ $branch->name }}</p>
</div>

<!-- Form Card -->
<div class="max-w-2xl">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <form action="{{ route('company.branches.update', [$company, $branch]) }}"
              method="POST"
              class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch Name *</label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $branch->name) }}"
                               required
                               placeholder="e.g. Jakarta Branch"
                               class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch Code *</label>
                        <input type="text"
                               name="code"
                               id="code"
                               value="{{ old('code', $branch->code) }}"
                               required
                               placeholder="e.g. JKT"
                               class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        @error('code')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email', $branch->email) }}"
                           placeholder="branch@example.com"
                           class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                    <input type="text"
                           name="phone"
                           id="phone"
                           value="{{ old('phone', $branch->phone) }}"
                           placeholder="+62 xxx xxxx xxxx"
                           class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    @error('phone')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <textarea name="address"
                              id="address"
                              rows="3"
                              placeholder="Full branch address"
                              class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors resize-none">{{ old('address', $branch->address) }}</textarea>
                    @error('address')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location Map -->
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">location_on</span>
                        Branch Location
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Click on the map to set your branch location</p>

                    <!-- Map Container -->
                    <div id="map" class="mb-4"></div>

                    <!-- Latitude & Longitude Display -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Latitude</label>
                            <input type="text"
                                   id="latitude"
                                   value="{{ old('latitude', $branch->latitude) }}"
                                   readonly
                                   class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                            <input type="hidden" name="latitude" id="latitude_input" value="{{ old('latitude', $branch->latitude) }}">
                        </div>
                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Longitude</label>
                            <input type="text"
                                   id="longitude"
                                   value="{{ old('longitude', $branch->longitude) }}"
                                   readonly
                                   class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                            <input type="hidden" name="longitude" id="longitude_input" value="{{ old('longitude', $branch->longitude) }}">
                        </div>
                    </div>
                    @error('latitude')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @error('longitude')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-border-light dark:border-border-dark">
                    <input type="checkbox"
                           name="is_active"
                           id="is_active"
                           value="1"
                           {{ $branch->is_active ? 'checked' : '' }}
                           class="h-4 w-4 text-primary focus:ring-primary border-gray-300 dark:border-gray-600 rounded">
                    <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <span class="font-medium">Active</span> - This branch will be visible and operational
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-6 border-t border-border-light dark:border-border-dark">
                <a href="{{ route('company.branches.index', $company) }}"
                   class="inline-flex items-center px-4 py-2 border border-border-light dark:border-border-dark text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <span class="material-symbols-outlined text-sm mr-1">cancel</span>
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition-colors">
                    <span class="material-symbols-outlined text-sm mr-1">save</span>
                    Update Branch
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Default to company location if no branch location set, otherwise Jakarta
        const defaultLat = {{ $branch->latitude ?? $company->latitude ?? '-6.2088' }};
        const defaultLng = {{ $branch->longitude ?? $company->longitude ?? '106.8456' }};
        const zoomLevel = {{ $branch->latitude ? '15' : '11' }};

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
