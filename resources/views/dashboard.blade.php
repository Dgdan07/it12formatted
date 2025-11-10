<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ATIN Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Company Color Variables */
        :root {
            --congress-blue: #06448a;
            --amber: #fac307;
            --white: #f8f9fa;;
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
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--congress-blue) 0%, var(--amber) 100%);
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
</head>
<body>
    <!-- Sidebar Only -->
    <nav class="sidebar">
        <div class="sidebar-content">
            <div class="logo-container">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/atin_logo.png') }}" alt="Company Logo" class="img-fluid me-3" style="max-height: 40px;">
                    <div>
                        <div class="fw-bold fs-4 mb-0" style="color: var(--congress-blue);">ATIN</div>
                        <div class="small text-muted mb-0" style="line-height: 1.2;">
                            Industrial Hardware<br>Supply Inc.
                        </div>
                    </div>
                </div>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link active" data-page="dashboard">
                        <i class="bi bi-speedometer2 me-3"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="users">
                        <i class="bi bi-people me-3"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#collapseInventory" class="nav-link collapsed" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseInventory">
                        <i class="bi bi-boxes me-3"></i>
                        <span>Inventory & Sourcing</span>
                        <i class="bi bi-chevron-down ms-auto"></i> 
                    </a>
                    
                    <div class="collapse" id="collapseInventory">
                        <ul class="nav flex-column ps-3">
                            <li class="nav-item">
                                <a href="/products" class="nav-link sub-link">
                                    <i class="bi bi-circle-fill me-3 sub-icon"></i>
                                    Product List
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/categories" class="nav-link sub-link">
                                    <i class="bi bi-circle-fill me-3 sub-icon"></i>
                                    Categories
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/suppliers" class="nav-link sub-link">
                                    <i class="bi bi-circle-fill me-3 sub-icon"></i>
                                    Suppliers
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="orders">
                        <i class="bi bi-cart me-3"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="analytics">
                        <i class="bi bi-graph-up me-3"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="reports">
                        <i class="bi bi-file-text me-3"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a href="#" class="nav-link" data-page="settings">
                        <i class="bi bi-gear me-3"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div> <!-- End sidebar-content -->
        
        <!-- User info at bottom -->
        <div class="p-4 border-top border-secondary">
            <div class="d-flex align-items-center">
                <div class="user-avatar me-3">JD</div>
                <div>
                    <div class="fw-bold" style="color: var(--congress-blue);">John Doe</div>
                    <small class="text-muted">Administrator</small>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Everything else is one big iframe -->
    <iframe id="mainIframe" class="main-iframe" src="about:blank"></iframe>

    <!-- Bootstrap JS -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const iframe = document.getElementById('mainIframe');
            const sidebarLinks = document.querySelectorAll('.sidebar .nav-link[data-page]');
            
            // Set initial iframe content with clean white background
            iframe.srcdoc = `
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        :root {
                            --congress-blue: #06448a;
                            --amber: #fac307;
                            --white: #ffffff;
                            --monza: #e20615;
                        }
                        
                        body { 
                            margin: 0; 
                            padding: 0; 
                            background: var(--white);
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            height: 100vh;
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        }
                        .welcome {
                            text-align: center;
                            padding: 3rem;
                        }
                        .welcome i {
                            font-size: 4rem;
                            margin-bottom: 1rem;
                            color: var(--amber);
                        }
                        .company-name {
                            color: var(--congress-blue);
                            font-weight: bold;
                        }
                    </style>
                </head>
                <body>
                    <div class="welcome">
                        <i class="bi bi-building"></i>
                        <h3>Welcome to <span class="company-name">ATIN</span> Admin</h3>
                        <p>Select a section from the sidebar to begin</p>
                    </div>
                </body>
                </html>
            `;
            
            // Sidebar navigation
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Update active state
                    sidebarLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    
                    const page = this.getAttribute('data-page');
                    const pageName = this.textContent.trim();
                    
                    // Set iframe content based on selected page
                    iframe.srcdoc = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <style>
                                :root {
                                    --congress-blue: #06448a;
                                    --amber: #fac307;
                                    --white: #ffffff;
                                    --monza: #e20615;
                                }
                                
                                body { 
                                    margin: 0; 
                                    padding: 0; 
                                    background: var(--white);
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                    height: 100vh;
                                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                                }
                                .content {
                                    text-align: center;
                                    padding: 3rem;
                                }
                                .content i {
                                    font-size: 4rem;
                                    margin-bottom: 1rem;
                                    color: var(--amber);
                                }
                                .section-name {
                                    color: var(--congress-blue);
                                }
                            </style>
                        </head>
                        <body>
                            <div class="content">
                                <i class="bi bi-${getPageIcon(page)}"></i>
                                <h3><span class="section-name">${pageName}</span> Section</h3>
                                <p>This is the ${pageName.toLowerCase()} management area</p>
                                <p><small>Add your content here later</small></p>
                            </div>
                        </body>
                        </html>
                    `;
                });
            });
            
            function getPageIcon(page) {
                const icons = {
                    'dashboard': 'speedometer2',
                    'users': 'people',
                    'products': 'box-seam',
                    'orders': 'cart',
                    'analytics': 'graph-up',
                    'reports': 'file-text',
                    'settings': 'gear'
                };
                return icons[page] || 'window';
            }
        });
    </script>
</body>
</html>