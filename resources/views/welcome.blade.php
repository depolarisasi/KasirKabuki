<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toaster Test - KasirBraga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-base-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-primary mb-4">Toaster Test Page</h1>
            <p class="text-lg text-base-content mb-8">
                If you see toast notifications, the official toaster is working correctly!
            </p>
            <div class="space-x-4">
                <a href="{{ url('/test-toaster') }}" class="btn btn-primary">Test Toaster Again</a>
                <a href="{{ url('/') }}" class="btn btn-secondary">Back to Home</a>
            </div>
        </div>
    </div>
     
</body>
</html> 