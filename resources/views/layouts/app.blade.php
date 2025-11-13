<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ATIN Admin')</title>
    
    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
    
    <style>
 
        
        /* Company Color Variables */
        :root {
            --congress-blue: #06448a;
            --amber: #fac307;
            --white: #f8f9fa;
            --monza: #e20615;
        }
        
        .sidebar {
            background: #f8f9fa;
            color: #333;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            padding-top: 20px;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-right: 1px solid #e9ecef;
        }
        
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 100px;
        }
        
        .sidebar-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-content::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 3px;
        }
        
        .sidebar-content::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 3px;
        }
        
        .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: #adb5bd;
        }
        
        .sidebar .nav-link {
            color: #495057;
            padding: 12px 25px;
            margin: 4px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            border: none;
        }
        
        .sidebar .nav-link:hover {
            background: #f8f9fa;
            color: var(--congress-blue);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--amber) 0%, #ffd43b 100%);
            color: var(--congress-blue);
            box-shadow: 0 4px 15px rgba(250, 195, 7, 0.3);
        }
        
        .sidebar .nav-link.collapsed {
            background: transparent;
        }
        .sidebar .nav-link .chevron {
            display: inline-block;               
            transform-origin: center center;
            transition: transform 0.28s cubic-bezier(.4,0,.2,1);
            pointer-events: none;                
        }

            .sidebar .nav-link[aria-expanded="true"] .chevron,
            .sidebar .nav-link.expanded .chevron {   
            transform: rotate(180deg);
        }
                    
        .sub-link {
            padding: 8px 15px 8px 35px !important;
            font-size: 0.9rem;
            margin: 2px 15px !important;
        }
        
        .sub-icon {
            font-size: 0.5rem;
        }
        
        .main-iframe {
            margin-left: 280px;
            width: calc(100vw - 280px);
            height: 100vh;
            border: none;
        }

        .main-content {
            margin-left: 280px;
            width: calc(100vw - 280px);
            height: 100vh;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--amber);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: bold;
            font-size: 16px;
            border: 3px solid #e9ecef;
        }
        
        .logo-container {
            padding: 0 25px 25px 25px;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 20px;
        }
        
        .notification-badge {
            background: var(--monza);
            color: var(--white);
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                text-align: center;
            }
            
            .sidebar .nav-link span {
                display: none;
            }
            
            .sidebar .logo-text {
                display: none;
            }
            
            .sidebar .nav-link i {
                margin-right: 0 !important;
                font-size: 1.2rem;
            }
            
            .sidebar .nav-link {
                padding: 15px;
                margin: 8px 10px;
                text-align: center;
            }
            
            .main-iframe {
                margin-left: 80px;
                width: calc(100vw - 80px);
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @include('components.sidebar')
    
    <!-- Everything else is one big iframe -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
        
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // If it's a collapse toggle (has data-bs-toggle="collapse"), don't prevent default
                if (this.getAttribute('data-bs-toggle') === 'collapse') {
                    // Let Bootstrap handle the collapse
                    return;
                }
                
                // If it's a regular navigation link (has href and not #), let it navigate normally
                const href = this.getAttribute('href');
                if (href && href !== '#') {
                    // Let the browser navigate to the page normally
                    return;
                }
                
                // Only prevent default for links that don't navigate anywhere
                e.preventDefault();
                
                // Update active state for non-navigation links
                sidebarLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Auto-expand sidebar sections based on current page
        const currentPath = window.location.pathname;
        if (currentPath.includes('/roles') || currentPath.includes('/users')) {
            const userCollapse = document.getElementById('collapseUser');
            if (userCollapse) {
                userCollapse.classList.add('show');
                const trigger = document.querySelector('[aria-controls="collapseUser"]');
                if (trigger) {
                    trigger.classList.remove('collapsed');
                }
            }
        }
        
        // Auto-expand inventory section if on related pages
        if (currentPath.includes('/products') || currentPath.includes('/categories') || currentPath.includes('/suppliers')) {
            const inventoryCollapse = document.getElementById('collapseInventory');
            if (inventoryCollapse) {
                inventoryCollapse.classList.add('show');
                const trigger = document.querySelector('[aria-controls="collapseInventory"]');
                if (trigger) {
                    trigger.classList.remove('collapsed');
                }
            }
        }
    });
    </script>
    
    @stack('scripts')
</body>
</html>