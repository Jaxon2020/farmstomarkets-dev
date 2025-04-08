@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Sidebar: Profile Overview -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-md p-6 sticky top-6">
                        <div class="flex flex-col items-center">
                            <!-- Avatar -->
                            <div class="w-24 h-24 rounded-full bg-gray-200 overflow-hidden mb-4">
                                <img src="{{ $user->avatar ?? asset('images/default-avatar.png') }}" alt="Profile Avatar" class="w-full h-full object-cover">
                            </div>
                            <!-- Name -->
                            <h2 class="text-2xl font-semibold text-gray-800">{{ $user->name }}</h2>
                            <!-- Bio -->
                            <p class="text-gray-600 text-sm mt-2 text-center">{{ $user->bio ?? 'Add a bio to tell people about yourself!' }}</p>
                            <!-- Stats -->
                            <div class="mt-6 w-full grid grid-cols-2 gap-4 text-center">
                                <div>
                                    <p class="text-lg font-medium text-gray-800">{{ $user->listings_count ?? 0 }}</p>
                                    <p class="text-sm text-gray-500">Listings</p>
                                </div>
                                <div>
                                    <p class="text-lg font-medium text-gray-800">{{ $user->sales_count ?? 0 }}</p>
                                    <p class="text-sm text-gray-500">Sales</p>
                                </div>
                            </div>
                            <!-- Edit Profile Button (Optional) -->
                            <a href="#edit-profile" class="mt-6 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                Edit Profile
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Main Content: Settings -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Profile Information -->
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b">
                            <h3 class="text-xl font-semibold text-gray-800">Profile Information</h3>
                            <p class="text-sm text-gray-600">Update your account's profile details</p>
                        </div>
                        <div class="p-6">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <!-- Update Password -->
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b">
                            <h3 class="text-xl font-semibold text-gray-800">Update Password</h3>
                            <p class="text-sm text-gray-600">Keep your account secure with a strong password</p>
                        </div>
                        <div class="p-6">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>

                    <!-- Delete Account -->
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b">
                            <h3 class="text-xl font-semibold text-gray-800">Delete Account</h3>
                            <p class="text-sm text-gray-600">Permanently remove your account</p>
                        </div>
                        <div class="p-6">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection