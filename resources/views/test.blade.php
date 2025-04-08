<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Page</title>
</head>
<body>
    <h1>Test Page</h1>
    <h2>Upload Image</h2>
    <form id="upload-form" enctype="multipart/form-data">
        @csrf
        <label for="image">Upload Image:</label>
        <input type="file" name="image" id="image" style="display: none;">
        <button type="button" onclick="document.getElementById('image').click()">Select Image</button>
        <button type="submit">Upload</button>
    </form>
    <div id="upload-result"></div>

    <h2>Insert Data into My Table</h2>
    <form id="insert-my-table-form">
        @csrf
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required>
        <label for="text">Text:</label>
        <textarea name="text" id="text" required></textarea>
        <button type="submit">Insert Data</button>
    </form>
    <div id="insert-my-table-result"></div>

    <h2>Create Listing</h2>
    <form id="create-listing-form" enctype="multipart/form-data">
        @csrf
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required>

        <label for="price">Price:</label>
        <input type="number" name="price" id="price" step="0.01" required>

        <label for="type">Type:</label>
        <input type="text" name="type" id="type" required>

        <label for="location">Location:</label>
        <input type="text" name="location" id="location" required>

        <label for="description">Description:</label>
        <textarea name="description" id="description" required></textarea>

        <label for="contact_email">Contact Email:</label>
        <input type="email" name="contact_email" id="contact_email">

        <label for="contact_phone">Contact Phone:</label>
        <input type="tel" name="contact_phone" id="contact_phone">

        <label for="preferred_contact">Preferred Contact:</label>
        <select name="preferred_contact" id="preferred_contact" required>
            <option value="email">Email</option>
            <option value="phone">Phone</option>
        </select>

        <label for="listing-image">Upload Image:</label>
        <input type="file" name="image" id="listing-image" style="display: none;" required>
        <button type="button" onclick="document.getElementById('listing-image').click()">Select Image</button>

        <button type="submit">Create Listing</button>
    </form>
    <div id="create-listing-result"></div>

    <script>
    // Log to confirm script is running
    console.log('Script loaded');

    // Upload form handler
    document.getElementById('upload-form').addEventListener('submit', async function(event) {
        event.preventDefault();
        console.log('Upload form submitted');

        const formData = new FormData(this);
        const fileInput = document.getElementById('image');
        const file = fileInput.files[0];

        if (!file) {
            document.getElementById('upload-result').textContent = 'No file selected.';
            return;
        }

        try {
            const response = await fetch('/test-upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                document.getElementById('upload-result').textContent = 'File uploaded successfully: ' + result.url;
            } else {
                document.getElementById('upload-result').textContent = 'Error: ' + result.message;
            }
        } catch (error) {
            console.error('Upload error:', error);
            document.getElementById('upload-result').textContent = 'Error: ' + error.message;
        }
    });

    // Insert my table handler (using backend route)
    const insertForm = document.getElementById('insert-my-table-form');
    if (insertForm) {
        console.log('Insert form found, attaching event listener');
        insertForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            console.log('Insert form submitted');

            const formData = new FormData(this);
            console.log('Form data:', Object.fromEntries(formData));

            try {
                const response = await fetch('/test-insert-my-table', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData,
                });

                const result = await response.json();
                console.log('Backend response:', result);

                if (result.success) {
                    document.getElementById('insert-my-table-result').textContent = 'Data inserted successfully: ' + JSON.stringify(result.data, null, 2);
                } else {
                    document.getElementById('insert-my-table-result').textContent = 'Error: ' + result.message;
                }
            } catch (error) {
                console.error('Error during fetch:', error);
                document.getElementById('insert-my-table-result').textContent = 'Error: ' + error.message;
            }
        });
    } else {
        console.error('Insert form not found');
    }

    // Create listing handler (using backend route)
    const createListingForm = document.getElementById('create-listing-form');
    if (createListingForm) {
        console.log('Create listing form found, attaching event listener');
        createListingForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            console.log('Create listing form submitted');

            const formData = new FormData(this);
            const fileInput = document.getElementById('listing-image');
            const file = fileInput.files[0];

            if (!file) {
                document.getElementById('create-listing-result').textContent = 'No image selected.';
                return;
            }

            console.log('Form data:', Object.fromEntries(formData));

            try {
                const response = await fetch('/test-create-listing', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData,
                });

                const result = await response.json();
                console.log('Backend response:', result);

                if (result.success) {
                    document.getElementById('create-listing-result').textContent = 'Listing created successfully: ' + JSON.stringify(result.data, null, 2);
                } else {
                    document.getElementById('create-listing-result').textContent = 'Error: ' + result.message;
                }
            } catch (error) {
                console.error('Error during fetch:', error);
                document.getElementById('create-listing-result').textContent = 'Error: ' + error.message;
            }
        });
    } else {
        console.error('Create listing form not found');
    }
    </script>
</body>
</html>