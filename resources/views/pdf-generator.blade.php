<!DOCTYPE html>
<html>
<head>
    <title>Gmail to PDF Generator</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        .container { max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 20px; background: #4285F4; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gmail to PDF Generator</h1>
        
        @if(!session('gmail_token'))
            <p><a href="/auth/google">Authenticate with Google</a> first</p>
        @endif
        
        <form method="POST" action="/generate-pdf">
            @csrf
            
            <div class="form-group">
                <label for="email1">First Email Address</label>
                <input type="email" name="email1" required>
            </div>
            
            <div class="form-group">
                <label for="email2">Second Email Address</label>
                <input type="email" name="email2" required>
            </div>
            
            <button type="submit" >Create PDF</button>
        </form>
    </div>
</body>
</html>