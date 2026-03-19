</div> </div> <nav class="mobile-bottom-nav d-md-none no-print d-flex justify-content-around align-items-center">
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Catcher'): ?>
    <a href="index.php" class="nav-link text-center <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
        <i class="fas fa-chart-pie d-block fs-4 mb-1"></i>
        <span>Dashboard</span>
    </a>
    <a href="dogs.php" class="nav-link text-center <?php echo ($current_page == 'dogs.php') ? 'active' : ''; ?>">
        <i class="fas fa-dog d-block fs-4 mb-1"></i>
        <span>Registry</span>
    </a>
    <?php endif; ?>
    <a href="scanner.php" class="nav-link text-center <?php echo ($current_page == 'scanner.php') ? 'active' : ''; ?>">
        <i class="fas fa-camera d-block fs-4 mb-1"></i>
        <span>Scanner</span>
    </a>
</nav>

<style>
    /* Premium Mobile Bottom Navigation */
    .mobile-bottom-nav {
        position: fixed; 
        bottom: 0; 
        left: 0; 
        right: 0;
        
        /* Glassmorphism Effect */
        background: rgba(255, 255, 255, 0.90);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        
        border-top: 1px solid rgba(0,0,0,0.05);
        border-top-left-radius: 1.5rem; 
        border-top-right-radius: 1.5rem;
        box-shadow: 0 -10px 30px rgba(0,0,0,0.05); 
        z-index: 1050;
        
        /* Safe Area Padding for modern phones (iPhone Home Bar) */
        padding-top: 12px;
        padding-bottom: calc(12px + env(safe-area-inset-bottom)); 
    }
    
    .mobile-bottom-nav .nav-link { 
        text-decoration: none; 
        color: #94a3b8; /* Slate gray for inactive */
        font-size: 0.65rem; 
        font-weight: 700; 
        flex: 1; 
        transition: all 0.2s ease;
    }
    
    .mobile-bottom-nav .nav-link.active { 
        color: var(--basco-primary); 
        transform: translateY(-2px); /* Slight pop up effect when active */
    }
    
    .mobile-bottom-nav .nav-link.active i {
        text-shadow: 0 4px 10px rgba(12, 74, 110, 0.2); /* Glowing shadow on active icon */
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<footer class="text-center py-4 text-muted small no-print d-none d-md-block">
    <div class="fw-bold" style="color: var(--basco-primary);">LGU Basco Pet Registry System</div>
    &copy; <?php echo date('Y'); ?> | Developed for the Office of MAO
</footer>

</body>
</html>