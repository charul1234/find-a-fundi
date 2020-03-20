<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

  <!-- Sidebar - Brand -->
  <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">    
    <div class="sidebar-brand-icon rotate-n-15">
      <i class="fas fa-globe"></i>
    </div>
    <div class="sidebar-brand-text mx-3">{{ config('app.name', 'Laravel') }}</div>
  </a>

  <!-- Divider -->
  <hr class="sidebar-divider my-0">

  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.dashboard') }}">
      <i class="fas fa-fw fa-tachometer-alt"></i>
      <span>Dashboard</span></a>
    </li>

  @can('admin.users.index')
  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.users.index') }}">
      <i class="fas fa-fw fa-users"></i>
      <span>Users</span></a>
    </li>
  @endcan

  @can('admin.categories.index')
  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.categories.index') }}">
      <i class="fas fa-fw fa-list"></i>
      <span>Categories</span></a>
    </li>
  @endcan

  @can('admin.packages.index')
  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.packages.index') }}">
      <i class="fas fa-fw fa-list"></i>
      <span>Packages</span></a>
    </li>
  @endcan
  
    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
      <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

  </ul>
<!-- End of Sidebar -->