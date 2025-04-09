<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account Section</title>
    <style>
        /* Base styles */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #1f2937;
        }

        .space-y-6 {
            margin-bottom: 1.5rem;
        }

        /* Header styles */
        h2 {
            font-size: 1.125rem;
            font-weight: 500;
            color: #111827;
        }

        p {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        /* Button styles */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .btn-danger {
            background-color: #dc2626;
            color: white;
            border: none;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background-color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            width: 100%;
            max-width: 28rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Form styles */
        .form-group {
            margin-top: 1.5rem;
        }

        .form-group input {
            width: 75%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: #374151;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .form-group input::placeholder {
            color: #9ca3af;
        }

        /* Error styles */
        .error {
            color: #dc2626;
            font-size: 0.75rem;
            margin-top: 0.5rem;
            display: block;
        }

        /* Flex utilities */
        .flex {
            display: flex;
        }

        .justify-end {
            justify-content: flex-end;
        }

        .ms-3 {
            margin-left: 0.75rem;
        }
    </style>
</head>
<body>
    <section class="space-y-6">
        <header>
            <h2>Delete Account</h2>
            <p>
                Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.
            </p>
        </header>

        <button class="btn btn-danger" onclick="openModal()">Delete Account</button>

        <div id="confirm-user-deletion" class="modal">
            <div class="modal-content">
                <form method="POST" action="/profile" onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.');">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">

                    <h2>Are you sure you want to delete your account?</h2>
                    <p>
                        Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
                    </p>

                    <div class="form-group">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Password"
                            required
                        >
                        @if ($errors->userDeletion->has('password'))
                            <span class="error">{{ $errors->userDeletion->get('password')[0] }}</span>
                        @elseif ($errors->userDeletion->has('userDeletion'))
                            <span class="error">{{ $errors->userDeletion->get('userDeletion')[0] }}</span>
                        @endif
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn btn-danger ms-3">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </section>


    <script>
        function openModal() {
            document.getElementById('confirm-user-deletion').classList.add('show');
        }

        function closeModal() {
            document.getElementById('confirm-user-deletion').classList.remove('show');
        }

        // Show modal if there are errors (simulating Laravel behavior)
        @if ($errors->userDeletion->isNotEmpty())
            openModal();
        @endif
    </script>
</body>
</html>