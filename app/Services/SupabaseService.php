<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SupabaseService extends SupabaseApiBase
{
    /**
     * Fetch all listings.
     */
    public function fetchListings(?string $authToken = null, ?string $userId = null): array
    {
        $queryParams = [
            'select' => 'id,title,price,type,location,image_url,user_id,created_at,contact_email,contact_phone,preferred_contact',
            'order' => 'created_at.desc',
        ];

        return $this->get('listings', $queryParams, $authToken);
    }

    /**
     * Fetch featured listings (e.g., the most recent 3 listings).
     */
    public function fetchFeaturedListings(?string $authToken = null): array
    {
        $listings = $this->fetchListings($authToken);
        return array_slice($listings, 0, 3);
    }

    /**
     * Fetch a single listing by ID.
     */
    public function fetchListingById(string $listingId, ?string $authToken = null): ?array
    {
        $queryParams = [
            'id' => 'eq.' . $listingId,
            'select' => 'id,title,price,type,location,image_url,user_id,created_at,contact_email,contact_phone,preferred_contact,description',
        ];

        $data = $this->get('listings', $queryParams, $authToken);
        return $data[0] ?? null;
    }

    /**
     * Fetch listings for a specific user.
     */
    public function fetchUserListings(string $userId, ?string $authToken = null): array
    {
        $queryParams = [
            'user_id' => 'eq.' . $userId,
            'select' => 'id,title,price,type,location,image_url,user_id,created_at,contact_email,contact_phone,preferred_contact,description',
            'order' => 'created_at.desc',
        ];

        return $this->get('listings', $queryParams, $authToken);
    }
    public function fetchLikedListings(string $userId, ?string $authToken = null): array
    {
        $queryParams = [
            'select' => 'listings(id,title,price,type,location,image_url,user_id,created_at,contact_email,contact_phone,preferred_contact,description)',
            'user_id' => 'eq.' . $userId,
            'order' => 'created_at.desc',
        ];

        // Fetch from the 'likes' table and join with 'listings'
        $data = $this->get('likes', $queryParams, $authToken);

        // Extract the listings from the response
        $likedListings = [];
        foreach ($data as $like) {
            if (isset($like['listings']) && is_array($like['listings'])) {
                $likedListings[] = $like['listings'];
            }
        }

        return $likedListings;
    }
    /**
     * Check if a user has liked a listing.
     */
    public function hasUserLikedListing(string $userId, string $listingId): bool
    {
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $userId) || !preg_match('/^[0-9a-fA-F-]{36}$/', $listingId)) {
            Log::error("Invalid UUIDs provided", ['user_id' => $userId, 'listing_id' => $listingId]);
            return false;
        }

        $queryParams = [
            'user_id' => 'eq.' . $userId,
            'listing_id' => 'eq.' . $listingId,
        ];

        return !empty($this->get('likes', $queryParams));
    }

    /**
     * Add a like to a listing.
     */
    public function addLike(string $userId, string $listingId): bool
    {
        $data = [
            'user_id' => $userId,
            'listing_id' => $listingId,
        ];

        return !is_null($this->post('likes', $data));
    }

    /**
     * Remove a like from a listing.
     */
    public function removeLike(string $userId, string $listingId): bool
    {
        $queryParams = [
            'user_id' => 'eq.' . $userId,
            'listing_id' => 'eq.' . $listingId,
        ];

        return $this->delete('likes', $queryParams);
    }

    /**
     * Create a new listing in Supabase.
     */
    public function createListing(array $data, ?string $authToken = null): ?array
    {
        $logger = Log::channel('supabase');
        $logger->info("Creating listing with data", ['data' => $data]);
    
        $userId = session('supabase_user_id');
        if (!$userId) {
            $logger->error("Supabase user ID not found in session during createListing.");
            return null;
        }
    
        $data['user_id'] = $userId;
    
        // Sanitize the data, but exclude image_url
        $sanitizedData = array_map(function ($value) {
            if (is_string($value)) {
                // Trim whitespace
                $value = trim($value);
                // Check if the string is valid UTF-8
                if (!mb_check_encoding($value, 'UTF-8')) {
                    // If not UTF-8, assume ISO-8859-1 and convert to UTF-8
                    $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                }
                // Replace unsafe characters, but allow spaces and some punctuation for readability
                $value = preg_replace('/[^a-zA-Z0-9\s\.,-]/', '_', $value);
                return $value;
            }
            return $value;
        }, $data);
    
        // Ensure image_url is not sanitized, as it's already a valid URL from Supabase
        if (isset($data['image_url'])) {
            $sanitizedData['image_url'] = $data['image_url']; // Use the original image_url without sanitization
        }
    
        $logger->info("Sanitized listing data", ['sanitized_data' => $sanitizedData]);
        return $this->post('listings', $sanitizedData, $authToken);
    }
    /**
     * Delete a listing from Supabase.
     */
    public function deleteListing(string $listingId, string $userId, ?string $authToken = null): bool
    {
        $queryParams = [
            'id' => 'eq.' . $listingId,
            'user_id' => 'eq.' . $userId,
        ];

        return $this->delete('listings', $queryParams, $authToken);
    }

    /**
     * Search listings based on criteria.
     */
    public function searchListings(array $postData, ?string $authToken = null, ?string $userId = null): array
    {
        $queryParams = [
            'select' => 'id,title,price,type,location,image_url,user_id,created_at,contact_email,contact_phone,preferred_contact',
            'order' => 'created_at.desc',
        ];

        if (!empty($postData['animal-type'])) {
            $queryParams['type'] = 'eq.' . $postData['animal-type'];
        }
        if (!empty($postData['location'])) {
            $queryParams['location'] = 'eq.' . $postData['location'];
        }
        if (!empty($postData['min-price'])) {
            $queryParams['price'] = 'gte.' . $postData['min-price'];
        }
        if (!empty($postData['max-price'])) {
            $queryParams['price'] = 'lte.' . $postData['max-price'];
        }

        return $this->get('listings', $queryParams, $authToken);
    }

    /**
     * Upload an image to Supabase Storage (unchanged).
     */
    /**
     * Upload an image to Supabase Storage.
     */
    public function uploadImage($image, $folder = 'public/'): ?string
    {
        $logger = Log::channel('supabase');
        $fileStream = null;

        try {
            $userId = session('supabase_user_id');
            $logger->info("Uploading image for user_id: {$userId}", [
                'user_id' => $userId,
                'folder' => $folder,
            ]);

            // Validate file type and size
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $mimeType = $image->getMimeType();
            $logger->debug("Checking image MIME type", [
                'mime_type' => $mimeType,
                'allowed_types' => $allowedTypes,
            ]);
            if (!in_array($mimeType, $allowedTypes)) {
                $logger->error("Invalid image type: " . $mimeType, [
                    'mime_type' => $mimeType,
                    'allowed_types' => $allowedTypes,
                ]);
                throw new \Exception("Only JPEG, PNG, and GIF images are allowed.");
            }

            $fileSize = $image->getSize();
            $logger->debug("Checking image size", [
                'file_size' => $fileSize,
                'max_size' => 5 * 1024 * 1024,
            ]);
            if ($fileSize > 5 * 1024 * 1024) {
                $logger->error("Image size exceeds 5 MB: " . $fileSize, [
                    'file_size' => $fileSize,
                    'max_size' => 5 * 1024 * 1024,
                ]);
                throw new \Exception("Image size exceeds 5 MB.");
            }

            // Sanitize the file name
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $sanitizedFileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName) . '.' . $extension;
            $logger->debug("Sanitized file name", [
                'original_name' => $originalName,
                'extension' => $extension,
                'sanitized_file_name' => $sanitizedFileName,
            ]);

            // Add a timestamp to avoid conflicts
            $fileName = time() . '_' . $sanitizedFileName;
            $filePath = $folder . $fileName;
            $logger->debug("Prepared file path", [
                'file_name' => $fileName,
                'folder' => $folder,
                'file_path' => $filePath,
            ]);

            // Open the file as a stream to handle binary data correctly
            $realPath = $image->getRealPath();
            $logger->debug("Opening file stream", [
                'real_path' => $realPath,
            ]);
            $fileStream = fopen($realPath, 'rb');
            if ($fileStream === false) {
                $logger->error("Failed to open file stream for: " . $realPath, [
                    'real_path' => $realPath,
                ]);
                throw new \Exception("Failed to open file stream.");
            }

            // Upload the file to Supabase using the laravel-supabase-flysystem driver
            $logger->info("Uploading file to Supabase", [
                'file_path' => $filePath,
            ]);
            $result = Storage::disk('supabase')->put($filePath, $fileStream, 'public');
            $logger->debug("Upload result", [
                'result' => $result,
            ]);

            if (!$result) {
                $logger->error("Failed to upload image to Supabase: " . $fileName, [
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                ]);
                throw new \Exception("Failed to upload image to Supabase.");
            }

            // Get the public URL
            $publicUrl = Storage::disk('supabase')->url($filePath);
            $logger->debug("Retrieved public URL", [
                'public_url' => $publicUrl,
                'file_path' => $filePath,
            ]);

            // If the URL returned by the driver isn't a full URL, construct it manually
            if (!filter_var($publicUrl, FILTER_VALIDATE_URL)) {
                $supabaseUrl = config('filesystems.disks.supabase.url');
                $bucketName = config('filesystems.disks.supabase.bucket');
                $constructedUrl = rtrim($supabaseUrl, '/') . '/storage/v1/object/public/' . $bucketName . '/' . $filePath;
                $logger->debug("Constructed public URL manually", [
                    'original_public_url' => $publicUrl,
                    'supabase_url' => $supabaseUrl,
                    'bucket_name' => $bucketName,
                    'constructed_url' => $constructedUrl,
                ]);
                $publicUrl = $constructedUrl;
            }

            $logger->info("Image uploaded successfully. Public URL: {$publicUrl}", [
                'public_url' => $publicUrl,
            ]);
            return $publicUrl;

        } catch (\Exception $e) {
            $logger->error("Error uploading image: " . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return null;
        } finally {
            if (is_resource($fileStream)) {
                fclose($fileStream);
                $logger->debug("File stream closed");
            } else {
                $logger->warning("File stream was not a valid resource, skipping fclose", [
                    'file_stream' => $fileStream,
                ]);
            }
        }
    }

    /**
     * Get the anon key.
     */
    public function getKey(): string
    {
        return $this->anonKey;
    }

    /**
     * Get the base URL.
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}