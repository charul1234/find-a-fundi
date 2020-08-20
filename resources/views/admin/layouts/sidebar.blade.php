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

  @can('admin.providers.index')
  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.providers.index') }}">
      <i class="fas fa-fw fa-users"></i>
      <span>Providers</span></a>
    </li>
  @endcan

  @can('admin.seekers.index')
  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.seekers.index') }}">
      <i class="fas fa-fw fa-users"></i>
      <span>Seekers</span></a>
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

  @can('admin.advertisements.index')
  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.advertisements.index') }}">
      <i class="fas fa-fw fa-ad"></i>
      <span>Advertisements</span></a>
    </li>
  @endcan

  @can('admin.faqs.index')
  <li class="nav-item">
    <a class="nav-link" href="{{ route('admin.faqs.index') }}">
      <i class="fas fa-fw fa-question-circle"></i>
      <span>FAQs</span></a>
    </li>
  @endcan

  <li class="nav-item">
      <a class="nav-link collapsed" href="javascript:;" data-toggle="collapse" data-target="#collapseMaster" aria-expanded="true" aria-controls="collapsePages">
        <i class="fas fa-fw fa-list"></i>
        <span>Master</span>
      </a>
      <div id="collapseMaster" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar" style="">
        <div class="bg-white py-2 collapse-inner rounded">
          @can('admin.countries.index')
          <a class="collapse-item" href="{{ route('admin.countries.index') }}">Countries</a>
          @endcan
          @can('admin.cities.index')
          <a class="collapse-item" href="{{ route('admin.cities.index') }}">Cities</a>
          @endcan
        </div>
      </div>
    </li>
  
    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
      <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

  </ul>
<!-- End of Sidebar -->