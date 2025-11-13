<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ATIN Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }
        
        :root {
            --congress-blue: #06448a;
            --amber: #fac307;
        }
        
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .login-header {
            background: white;
            color: black;
            border-radius: 15px 15px 0 0;
            padding: 30px;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--amber) 0%, #ffd43b 100%);
            border: none;
            color: var(--congress-blue);
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #ffd43b 0%, var(--amber) 100%);
            color: var(--congress-blue);
        }
        
        .form-control:focus {
            border-color: var(--amber);
            box-shadow: 0 0 0 0.2rem rgba(250, 195, 7, 0.25);
        }
        
        .alert {
            border: none;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="login-card card">
        <div class="login-header">
            <div class="d-flex align-items-center justify-content-center">
                <div>
                    <img src="{{ asset('images/atin_logo.png') }}" alt="Company Logo" class="img-fluid me-3" style="max-height: 100px;">
                    <h4 class="mb-0 fw-bold">ATIN</h4>
                    <small class="opacity-75">Industrial Hardware Supply Inc.</small>
                </div>
            </div>
        </div>
        
        <div class="card-body p-4">
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif
            
            <form method="POST" action="/login">
                @csrf
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" class="form-control" 
                               id="username" name="username" 
                               value="{{ old('username') }}"
                               placeholder="Enter your username" required autofocus>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                      </span>
                      <input type="password" class="form-control" 
                             id="password" name="password" 
                             placeholder="Enter your password" required>
                      <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                        <i class="bi bi-eye"></i>
                      </span>
                    </div>
                  </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Sign In
                </button>
            </form>
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.querySelector("#togglePassword");
        const passwordInput = document.querySelector("#password");
      
        togglePassword.addEventListener("click", function () {
          const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
          passwordInput.setAttribute("type", type);
      
          // toggle the eye / eye-slash icon
          this.querySelector("i").classList.toggle("bi-eye");
          this.querySelector("i").classList.toggle("bi-eye-slash");
        });
      </script>
</body>
</html>