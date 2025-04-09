<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

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
    public function fetchUserListings(string $userId, ?string $authToken = null, ?array $searchCriteria = []): array
    {
        $queryParams = [
            'user_id' => 'eq.' . $userId,
            'select' => 'id,title,price,type,location,image_url,user_id,created_at,contact_email,contact_phone,preferred_contact,description',
            'order' => 'created_at.desc',
        ];

        // Apply search criteria
        if (!empty($searchCriteria['animal-type'])) {
            $queryParams['type'] = 'eq.' . $searchCriteria['animal-type'];
        }
        if (!empty($searchCriteria['location'])) {
            $queryParams['location'] = 'eq.' . $searchCriteria['location'];
        }
        if (!empty($searchCriteria['min-price'])) {
            $queryParams['price'] = 'gte.' . $searchCriteria['min-price'];
        }
        if (!empty($searchCriteria['max-price'])) {
            $queryParams['price'] = 'lte.' . $searchCriteria['max-price'];
        }

        return $this->get('listings', $queryParams, $authToken);
    }

    public function fetchLikedListings(string $userId, ?string $authToken = null, ?array $searchCriteria = []): array
    {
        if (empty($userId)) {
            Log::error("Invalid user ID for fetchLikedListings", ['user_id' => $userId]);
            return [];
        }

        try {
            // First get the liked listing IDs
            $queryParams = [
                'user_id' => 'eq.' . $userId,
                'select' => 'listing_id',
            ];

            $likes = $this->get('likes', $queryParams, $authToken);
            if (empty($likes)) {
                return [];
            }

            // Extract listing IDs
            $listingIds = array_map(function($like) {
                return $like['listing_id'];
            }, $likes);

            if (empty($listingIds)) {
                return [];
            }

            // Then fetch the actual listings with search criteria
            $listingIdList = implode(',', $listingIds);
            $queryParams = [
                'id' => 'in.(' . $listingIdList . ')',
                'select' => 'id,title,price,type,location,image_url,user_id,created_at,contact_email,contact_phone,preferred_contact',
                'order' => 'created_at.desc',
            ];

            // Apply search criteria
            if (!empty($searchCriteria['animal-type'])) {
                $queryParams['type'] = 'eq.' . $searchCriteria['animal-type'];
            }
            if (!empty($searchCriteria['location'])) {
                $queryParams['location'] = 'eq.' . $searchCriteria['location'];
            }
            if (!empty($searchCriteria['min-price'])) {
                $queryParams['price'] = 'gte.' . $searchCriteria['min-price'];
            }
            if (!empty($searchCriteria['max-price'])) {
                $queryParams['price'] = 'lte.' . $searchCriteria['max-price'];
            }

            return $this->get('listings', $queryParams, $authToken);
        } catch (\Exception $e) {
            Log::error("Error fetching liked listings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if a user has liked a listing.
     */
    public function hasUserLikedListing(string $userId, string $listingId): bool
    {
        if (empty($userId) || empty($listingId)) {
            Log::error("Invalid parameters for hasUserLikedListing", ['user_id' => $userId, 'listing_id' => $listingId]);
            return false;
        }

        try {
            $queryParams = [
                'user_id' => 'eq.' . $userId,
                'listing_id' => 'eq.' . $listingId,
            ];

            $result = $this->get('likes', $queryParams);
            return !empty($result);
        } catch (\Exception $e) {
            Log::error("Error checking if user liked listing: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a like to a listing.
     */
    public function addLike(string $userId, string $listingId): bool
    {
        if (empty($userId) || empty($listingId)) {
            Log::error("Invalid parameters for addLike", ['user_id' => $userId, 'listing_id' => $listingId]);
            return false;
        }

        try {
            $data = [
                'user_id' => $userId,
                'listing_id' => $listingId,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $result = $this->post('likes', $data);
            return !is_null($result);
        } catch (\Exception $e) {
            Log::error("Error adding like: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a like from a listing.
     */
    public function removeLike(string $userId, string $listingId): bool
    {
        if (empty($userId) || empty($listingId)) {
            Log::error("Invalid parameters for removeLike", ['user_id' => $userId, 'listing_id' => $listingId]);
            return false;
        }

        try {
            $queryParams = [
                'user_id' => 'eq.' . $userId,
                'listing_id' => 'eq.' . $listingId,
            ];

            return $this->delete('likes', $queryParams);
        } catch (\Exception $e) {
            Log::error("Error removing like: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new listing in Supabase.
     */
    public function createListing(array $data, ?string $authToken = null): ?array
    {
        $logger = Log::channel('supabase');
        $logger->info('Creating listing in Supabase', ['data' => $data]);

        try {
            $result = $this->post('listings', $data, $authToken);
            if (!empty($result)) {
                $logger->info('Listing created successfully', ['result' => $result]);
                return $result;
            }
            $logger->warning('No data returned from Supabase after creating listing');
            return null;
        } catch (\Exception $e) {
            $logger->error('Failed to create listing in Supabase: ' . $e->getMessage());
            return null;
        }
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

    public function __construct()
    {
        parent::__construct(); // Call parent constructor to set $baseUrl, $anonKey, etc.
        $this->url = env('SUPABASE_URL'); // Set the root URL for Storage API
    }

    /**
     * Upload an image to Supabase Storage using direct HTTP request.
     */
    public function uploadImage($image, $folder = 'public/', ?string $authToken = null): ?string
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
            
            if (!in_array($mimeType, $allowedTypes)) {
                throw new \Exception("Only JPEG, PNG, and GIF images are allowed.");
            }

            // Check file size (5MB limit)
            $fileSize = $image->getSize();
            if ($fileSize > 5 * 1024 * 1024) {
                throw new \Exception("Image size exceeds 5 MB.");
            }

            // Sanitize the file name to ensure UTF-8 compatibility
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            
            // Ensure filename is valid UTF-8
            if (!mb_check_encoding($originalName, 'UTF-8')) {
                $originalName = mb_convert_encoding($originalName, 'UTF-8', 'ISO-8859-1');
            }
            
            $sanitizedFileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName) . '.' . $extension;
            $fileName = time() . '_' . $sanitizedFileName;
            $filePath = $folder . $fileName;
            
            // Open the file as a stream to handle binary data correctly
            $realPath = $image->getRealPath();
            $fileStream = fopen($realPath, 'rb');
            if ($fileStream === false) {
                $logger->error("Failed to open file stream for: " . $realPath);
                throw new \Exception("Failed to open file stream.");
            }

            // Prepare the URL for the Supabase Storage API
            $bucketName = 'listings-images';
            $storageUrl = rtrim($this->url, '/') . '/storage/v1/object/' . $bucketName . '/' . $filePath;
            
            // Use the authenticated user's token if provided, otherwise fall back to anonKey
            $token = $authToken ?? $this->anonKey;
            
            // Make the HTTP request to upload the file using a stream
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => $mimeType,
            ])->withBody(stream_get_contents($fileStream), $mimeType)->post($storageUrl);
            
            if (!$response->successful()) {
                $logger->error("Failed to upload image to Supabase", [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                throw new \Exception("Failed to upload image to Supabase: " . $response->body());
            }
            
            // Construct the public URL
            $publicUrl = rtrim($this->url, '/') . '/storage/v1/object/public/' . $bucketName . '/' . $filePath;
            
            $logger->info("Image uploaded successfully. Public URL: {$publicUrl}");
            return $publicUrl;
            
        } catch (\Exception $e) {
            $logger->error("Exception during image upload: " . $e->getMessage(), [
                'exception' => $e->getMessage(),
            ]);
            return null;
        } finally {
            if (is_resource($fileStream)) {
                fclose($fileStream);
                $logger->debug("File stream closed");
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