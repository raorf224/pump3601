<!-- Begin Header -->
<header class="app-header" id="appHeader">
    <div class="container-fluid w-100">
        <div class="d-flex align-items-center">
            <div class="me-auto">
                <div class="d-inline-flex align-items-center gap-5">
                    <a href="station-sites" class="fs-18 fw-semibold">
                        <!-- Pump 360 Logo with orange filter and larger size -->
                        <img height="40" class="header-sidebar-logo-default" alt="Pump 360 Logo"
                            src="https://pump360.pk/wp/wp-content/uploads/2025/01/logo-sized-1.png"
                            style="filter: brightness(0) saturate(100%) invert(51%) sepia(93%) saturate(745%) hue-rotate(350deg) brightness(95%) contrast(92%);">
                    </a>
                    <button type="button"
                        class="vertical-toggle btn btn-light-light text-muted icon-btn fs-5 rounded-pill"
                        id="toggleSidebar">
                        <i class="bi bi-arrow-bar-left header-icon"></i>
                    </button>
                    <button type="button"
                        class="horizontal-toggle btn btn-light-light text-muted icon-btn fs-5 rounded-pill d-none"
                        id="toggleHorizontal">
                        <i class="ri-menu-2-line header-icon"></i>
                    </button>

                </div>
            </div>
            <div class="flex-shrink-0 d-flex align-items-center gap-1">

                <!-- <button class="btn header-btn d-none d-md-block" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                    <i class="bi bi-gear"></i>
                </button> -->
                <div class="dark-mode-btn" id="toggleMode">
                    <button class="btn header-btn active" id="lightModeBtn">
                        <i class="bi bi-brightness-high"></i>
                    </button>
                    <button class="btn header-btn" id="darkModeBtn">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                </div>
                <div class="dropdown pe-dropdown-mega d-none d-md-block">

                    <div class="dropdown-menu dropdown-mega-md header-dropdown-menu pe-noti-dropdown-menu p-0">
                        <div class="p-3 border-bottom">
                            <h6 class="d-flex align-items-center mb-0">Notification <span
                                    class="badge bg-success rounded-circle align-middle ms-1">4</span></h6>
                        </div>
                        <div class="p-3">
                            <div class="noti-item">
                                <!-- Notification logo -->
                                <img src="https://pump360.pk/wp/wp-content/uploads/2025/01/logo-sized-1.png"
                                    alt="Pump 360 Logo" class="avatar-md"
                                    style="filter: brightness(0) saturate(100%) invert(51%) sepia(93%) saturate(745%) hue-rotate(350deg) brightness(95%) contrast(92%);">
                                <div>
                                    <a href="javascript:void(0)" class="stretched-link">
                                        <h6 class="mb-1">Item Back in Stock</h6>
                                    </a>
                                    <p class="text-muted mb-2">Today, 02:45 PM</p>
                                    <div class="p-2 bg-body-tertiary bg-opacity-50 rounded">
                                        <h6 class="mb-0 lh-base">Good news! The item you wanted is back in stock. Grab
                                            it before it’s gone again!</h6>
                                    </div>
                                </div>
                                <a href="javascript:void(0)"
                                    class="position-absolute top-10 end-0 fs-18 z-1 link link-danger"><i
                                        class="bi bi-x"></i></a>
                            </div>
                            <div class="noti-item">
                                <img src="{{ asset('assets/images/avatar/avatar-8.jpg') }}" alt="Avatar Image"
                                    class="avatar-md">
                                <div>
                                    <a href="javascript:void(0)" class="stretched-link">
                                        <h6 class="mb-1 text-muted"><strong
                                                class="fw-semibold text-body">Donald</strong> liked your post</h6>
                                    </a>
                                    <p class="text-muted mb-0">Friday, 11:29 PM</p>
                                </div>
                                <a href="javascript:void(0)"
                                    class="position-absolute top-10 end-0 fs-18 z-1 link link-danger"><i
                                        class="bi bi-x"></i></a>
                            </div>
                            <div class="noti-item">
                                <div
                                    class="avatar-md d-flex align-items-center justify-content-center bg-primary-subtle text-primary fs-16">
                                    <i class="bi bi-fire"></i>
                                </div>
                                <div>
                                    <a href="javascript:void(0)" class="stretched-link">
                                        <h6 class="mb-1">Birthday Reminder</h6>
                                    </a>
                                    <p class="text-muted mb-2">Tuesday, 02:45 PM</p>
                                    <div class="p-2 bg-body-tertiary bg-opacity-50 rounded">
                                        <h6 class="mb-0 lh-base">Don’t forget! It’s Emily birthday tomorrow. Send them a
                                            message!</h6>
                                    </div>
                                </div>
                                <a href="javascript:void(0)"
                                    class="position-absolute top-10 end-0 fs-18 z-1 link link-danger"><i
                                        class="bi bi-x"></i></a>
                            </div>
                            <div class="noti-item">
                                <img src="{{ asset('assets/images/avatar/avatar-5.jpg') }}" alt="Avatar Image"
                                    class="avatar-md">
                                <div>
                                    <a href="javascript:void(0)" class="stretched-link">
                                        <h6 class="mb-1 text-muted"><strong
                                                class="fw-semibold text-body">Richard</strong> followed you</h6>
                                    </a>
                                    <p class="text-muted mb-0">Monday, 07:14 AM</p>
                                </div>
                                <a href="javascript:void(0)"
                                    class="position-absolute top-10 end-0 fs-18 z-1 link link-danger"><i
                                        class="bi bi-x"></i></a>
                            </div>
                            <div class="noti-item">
                                <img src="{{ asset('assets/images/avatar/avatar-6.jpg') }}" alt="Avatar Image"
                                    class="avatar-md">
                                <div>
                                    <a href="javascript:void(0)" class="stretched-link">
                                        <h6 class="mb-1 text-muted"><strong
                                                class="fw-semibold text-body">Daniel</strong> invited you to join
                                            <strong class="fw-semibold text-body">Website Redesign</strong></h6>
                                    </a>
                                    <p class="text-muted mb-2">Thursday, 5:10 PM</p>
                                    <div class="d-flex align-items-center gap-1 flex-wrap position-relative z-1">
                                        <button class="btn btn-danger btn-sm">Decline</button>
                                        <button class="btn btn-primary btn-sm">Accept</button>
                                    </div>
                                </div>
                                <a href="javascript:void(0)"
                                    class="position-absolute top-10 end-0 fs-18 z-1 link link-danger"><i
                                        class="bi bi-x"></i></a>
                            </div>
                            <div class="noti-item">
                                <img src="{{ asset('assets/images/avatar/avatar-4.jpg') }}" alt="Avatar Image"
                                    class="avatar-md">
                                <div>
                                    <a href="javascript:void(0)" class="stretched-link">
                                        <h6 class="mb-1 text-muted"><strong
                                                class="fw-semibold text-body">Olivia</strong> liked your recent post
                                        </h6>
                                    </a>
                                    <p class="text-muted mb-0">Thursday 3:20 PM</p>
                                </div>
                                <a href="javascript:void(0)"
                                    class="position-absolute top-10 end-0 fs-18 z-1 link link-danger"><i
                                        class="bi bi-x"></i></a>
                            </div>
                            <div class="noti-item">
                                <img src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" alt="Avatar Image"
                                    class="avatar-md">
                                <div>
                                    <a href="javascript:void(0)" class="stretched-link">
                                        <h6 class="mb-1 text-muted"><strong class="fw-semibold text-body">Mia</strong>
                                            shared a file in Marketing Campaign</h6>
                                    </a>
                                    <p class="text-muted mb-2">Thursday 3:20 PM</p>
                                    <div
                                        class="d-flex align-items-center gap-2 p-1 position-relative z-1 border rounded">
                                        <div
                                            class="avatar-md d-flex align-items-center rounded justify-content-center flex-shrink-0 bg-danger-subtle text-danger">
                                            <i class="bi bi-file-pdf"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <a href="javascript:void(0)">
                                                <h6 class="mb-1">Campaign_Strategy.mp4</h6>
                                            </a>
                                            <p class="mb-0 text-muted">MP4 | 14 MB</p>
                                        </div>
                                    </div>
                                </div>
                                <a href="javascript:void(0)"
                                    class="position-absolute top-10 end-0 fs-18 z-1 link link-danger"><i
                                        class="bi bi-x"></i></a>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="dropdown pe-dropdown-mega d-none d-md-block">
                    <button class="header-profile-btn btn gap-1 text-start" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <span class="header-btn btn position-relative">
                            <img src="{{ asset('assets/images/avatar/avatar-10.jpg') }}" alt="Avatar Image"
                                class="img-fluid rounded-circle">
                            <span
                                class="position-absolute translate-middle badge border border-light rounded-circle bg-success"><span
                                    class="visually-hidden">unread messages</span></span>
                        </span>
                        <div class="d-none d-lg-block pe-2">
                            <span class="d-block mb-0 fs-13 fw-semibold">{{Auth::user()->full_name}}</span>
                            <span class="d-block mb-0 fs-12 text-muted">{{Auth::user()->role}}</span>
                        </div>
                    </button>
                    <div class="dropdown-menu dropdown-mega-sm header-dropdown-menu p-3">
                        <div class="border-bottom pb-2 mb-2 d-flex align-items-center gap-2">
                            <img src="{{ asset('assets/images/avatar/avatar-10.jpg') }}" alt="Avatar Image"
                                class="avatar-md">
                            <div>
                                <a href="javascript:void(0)">
                                    <h6 class="mb-0 lh-base">{{Auth::user()->full_name}}</h6>
                                </a>
                                <p class="mb-0 fs-13 text-muted">{{Auth::user()->role}}</p>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-1 border-bottom pb-1">
                            <!-- <li><a class="dropdown-item" href="javascript:void(0)"><i class="bi bi-person me-1"></i> View Profile</a></li> -->
                            <!-- <li><a class="dropdown-item" href="javascript:void(0)"><i class="bi bi-gear me-1"></i> Settings</a></li> -->
                            <!-- <li><a class="dropdown-item" href="javascript:void(0)"><i class="bi bi-award me-1"></i> Subscription</a></li> -->
                        </ul>
                        <ul class="list-unstyled mb-1 border-bottom pb-1">
                            <!-- <li><a class="dropdown-item" href="javascript:void(0)"><i class="bi bi-clock me-1"></i> ChangLog</a></li> -->
                            <!-- <li><a class="dropdown-item" href="javascript:void(0)"><i class="bi bi-people me-1"></i> Team</a></li> -->
                            <!-- <li><a class="dropdown-item" href="javascript:void(0)"><i class="bi bi-headset me-1"></i> Support</a></li> -->
                        </ul>
                        <ul class="list-unstyled mb-0">
                            <li><a class="dropdown-item" href="{{ route('logout') }}"><i
                                        class="bi bi-box-arrow-right me-1"></i> Sign Out</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- END Header -->

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 bg-transparent">
            <div class="d-flex justify-content-between align-items-center bg-body">
                <div class="d-flex align-items-center border-0 px-3">
                    <i class="bi bi-search me-2"></i>
                    <input class="d-flex w-full py-3 bg-transparent border-0 focus-ring" placeholder="Search Here.."
                        autocomplete="off" autocorrect="off" spellcheck="false" aria-autocomplete="list" role="combobox"
                        aria-expanded="true" type="text">
                </div>
                <button type="button" class="btn-close pe-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-body mt-4">
                <p class="font-normal mb-2">Searching For...</p>
                <span class="badge bg-light-subtle border text-body">Analytics <i class="ri-close-line"></i></span>
                <span class="badge bg-light-subtle border text-body">Project <i class="ri-close-line"></i></span>
                <span class="badge bg-light-subtle border text-body">Eccomerce <i class="ri-close-line"></i></span>
                <span class="badge bg-light-subtle border text-body">CRM <i class="ri-close-line"></i></span>
                <span class="badge bg-light-subtle border text-body">Logistics <i class="ri-close-line"></i></span>
                <span class="badge bg-light-subtle border text-body">Academy <i class="ri-close-line"></i></span>
            </div>
        </div>
    </div>
</div>