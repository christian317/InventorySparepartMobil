<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --sidebar-width: 250px;
        }

        .wrapper {
            display: flex;
            min-height: 100vh; 
        }

        .sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0; 
            background-color: #343a40; 
            color: white;
            padding: 1rem 0;
            transition: transform 0.3s ease;
        }

        .main-content {
            flex-grow: 1;
            padding: 1rem;
            width: calc(100% - var(--sidebar-width)); 
        }

        .mobile-navbar {
            display: none;
            background-color: #343a40;
            color: white;
            padding: 0.5rem 1rem;
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 1050; 
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                width: 100%;
                padding-left: 0;
            }

            .mobile-navbar {
                display: block;
            }
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        /* Style untuk link aktif */
        .sidebar .active {
             color: white !important;
             background-color: rgba(255, 255, 255, 0.2);
             font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="mobile-navbar sticky-top">
        <div class="d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-white" href="#">Inventory</a>
            <button class="btn btn-dark d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </div>
    
    <div class="wrapper">
        
        <nav class="sidebar collapse d-lg-block" id="sidebarMenu">
            <div class="position-sticky">
                <a class="navbar-brand text-white d-none d-lg-block p-3" href="#">
                    <h4><i class="bi bi-box-seam"></i> Inventory</h4>
                </a>
                <hr class="text-white-50 d-lg-block d-none">

                <ul class="nav flex-column ps-3 pe-3">
                    
                    <li class="nav-item">
                        <a class="nav-link @if(Request::routeIs('dashboard')) active @endif" href="{{ route('dashboard') }}"><i class="bi bi-house-door-fill me-2"></i> Dashboard</a>
                    </li>
                
                    <li class="nav-item">
                        <a class="nav-link @if(Request::routeIs('produk.*')) active @endif" href="{{ route('produk.index') }}"><i class="bi bi-box-seam-fill me-2"></i> Produk</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link @if(Request::routeIs('orders.*')) active @endif" href="{{ route('orders.index') }}"><i class="bi bi-cart-fill me-2"></i> Pesanan</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link @if(Request::routeIs('returns.*')) active @endif" href="{{ route('returns.index') }}"><i class="bi bi-arrow-return-left me-2"></i> Retur</a>
                    </li>
                </ul>
            </div>
        </nav>
        <main class="main-content">
            @yield('content')
        </main>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.querySelectorAll('#sidebarMenu a').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth < 992) { 
                    const sidebar = document.getElementById('sidebarMenu');
                    const bsCollapse = bootstrap.Collapse.getInstance(sidebar);
                    if (bsCollapse) {
                         bsCollapse.hide();
                    }
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>