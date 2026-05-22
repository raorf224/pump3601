

<aside class="pe-app-sidebar horizontal-sidebar" id="horizontal-aside">
    <div class="pe-app-sidebar-logo px-6 d-flex align-items-center position-relative">
        <!-- Brand -->
        <a href="/index" class="fs-18 fw-semibold">
            <img height="30" class="pe-app-sidebar-logo-default d-none" alt="Logo"
                src="{{ asset('assets/images/logo-dark.png') }}">
            <img height="30" class="pe-app-sidebar-logo-light d-none" alt="Logo"
                src="{{ asset('assets/images/logo-light.png') }}">
            <img height="30" class="pe-app-sidebar-logo-minimize d-none" alt="Logo"
                src="{{ asset('assets/images/logo-md.png') }}">
            <img height="30" class="pe-app-sidebar-logo-minimize-light d-none" alt="Logo"
                src="{{ asset('assets/images/logo-md-light.png') }}">
        </a>
    </div>

   <nav class="pe-app-sidebar-menu nav nav-pills">

        <ul class="pe-horizontal-menu list-unstyled" id="horizontal-menu">

            <!-- Main -->
            <li class="pe-menu-title">Main</li>

            <li class="pe-slide">
                <a href="{{url('sale-report')}}" class="pe-nav-link">
                    <i class="bi bi-speedometer2 pe-nav-icon"></i>
                    <span class="pe-nav-content">Dashboard</span>
                </a>
            </li>

            <!-- Station Setup -->
            <li class="pe-menu-title">Station Setup</li>
            <li class="pe-slide pe-has-sub">
                <a href="#collapseStation" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false"
                    aria-controls="collapseStation">
                    <i class="bi bi-fuel-pump pe-nav-icon"></i>
                    <span class="pe-nav-content">Station Setup</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse" id="collapseStation">
                    <li class="pe-slide">
                        <a href="{{url('station-sites')}}" data-page="sites" class="pe-nav-link">
                            <i class="bi bi-geo-alt pe-nav-icon"></i>
                            <span class="pe-nav-content">Sites</span>
                        </a>
                    </li>
                    <li class="pe-slide">
                        <a href="{{url('products-setup')}}" data-page="product" class="pe-nav-link">
                            <i class="bi bi-box-seam pe-nav-icon"></i>
                            <span class="pe-nav-content">Product</span>
                        </a>
                    </li>
                    <li class="pe-slide">
                        <a href="{{url('tanks-visualization')}}" data-page="tanks" class="pe-nav-link">
                            <i class="bi bi-droplet-half pe-nav-icon"></i>
                            <span class="pe-nav-content">Tanks</span>
                        </a>
                    </li>
                    <li class="pe-slide">
                        <a href="{{url('dispenser-visualization')}}" data-page="dispenser" class="pe-nav-link">
                            <i class="bi bi-speedometer2 pe-nav-icon"></i>
                            <span class="pe-nav-content">Dispenser</span>
                        </a>
                    </li>
                    <li class="pe-slide">
                        <a href="{{url('nozzel-visualization')}}" data-page="nozzels" class="pe-nav-link">
                            <i class="bi bi-funnel pe-nav-icon"></i>
                            <span class="pe-nav-content">Nozzles</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Separate Menu Items -->
            <li class="pe-menu-title">Management</li>

            @if(Auth::check() && strtolower(Auth::user()->role) === 'admin')
            <li class="pe-slide">
                <a href="{{url('users')}}" data-page="users" class="pe-nav-link">
                    <i class="bi bi-person-gear pe-nav-icon"></i>
                    <span class="pe-nav-content">Users</span>
                </a>
            </li>
            @endif

            <li class="pe-menu-title">Purchase</li>
            <li class="pe-slide pe-has-sub">
                <a href="#collapseReports" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false"
                    aria-controls="collapseReports">
                    <i class="bi bi-cart-check pe-nav-icon"></i>
                    <span class="pe-nav-content">Purchase</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse" id="collapseReports">
                    <li class="pe-slide">
                        <a href="{{url('oil-purchase')}}" class="pe-nav-link" data-page="oilpurchase">
                            <i class="bi bi-fuel-pump pe-nav-icon"></i>
                            <span class="pe-nav-content">Oil</span>
                        </a>
                    </li>
                    <li class="pe-slide">
                        <a href="{{url('lube-purchase')}}" class="pe-nav-link" data-page="lubricants">
                            <i class="bi bi-droplet-half pe-nav-icon"></i>
                            <span class="pe-nav-content">Lubricants</span>
                        </a>
                    </li>
                </ul>
            </li>


            <li class="pe-menu-title">Accounts & Transactions</li>
            <li class="pe-slide pe-has-sub">
                <a href="#collapseAccountsTransactions" class="pe-nav-link" data-bs-toggle="collapse"
                    aria-expanded="false" aria-controls="collapseAccountsTransactions">
                    <i class="bi bi-wallet2 pe-nav-icon"></i>
                    <span class="pe-nav-content">Accounts & Transactions</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse" id="collapseAccountsTransactions">
                    <li class="pe-slide">
                        <a href="{{url('accounts')}}" class="pe-nav-link" data-page="accounts">
                            <i class="bi bi-person-lines-fill pe-nav-icon"></i>
                            <span class="pe-nav-content">Accounts</span>
                        </a>
                    </li>
                    <li class="pe-slide">
                        <a href="{{url('transactions')}}" class="pe-nav-link" data-page="transactions">
                            <i class="bi bi-receipt-cutoff pe-nav-icon"></i>
                            <span class="pe-nav-content">Transactions</span>
                        </a>
                    </li>
					<li class="pe-slide">
                        <a href="{{url('site_amount_workflow')}}"   data-page="bank" class="pe-nav-link" data-page="Bank Deposit">
                            <i class="bi bi-cash-coin pe-nav-icon"></i> 
                            <span class="pe-nav-content">Bank Deposit</span>
                        </a>
                    </li>
					<li class="pe-slide">
						<a href="{{url('received-amount')}}" class="pe-nav-link" data-page="received-amount">
							<i class="bi bi-credit-card-2-front pe-nav-icon"></i>
							<span class="pe-nav-content">Received Amount</span>
						</a>
					</li>
                </ul>
            </li>
			


            <li class="pe-slide">
                <a href="{{url('employe')}}" class="pe-nav-link">
                    <i class="bi bi-people pe-nav-icon"></i>
                    <span class="pe-nav-content">Employee</span>
                </a>
            </li>

                 <!-- <li class="pe-menu-title">Employee Management</li>
            <li class="pe-slide pe-has-sub">
                <a href="#collapseEmployee" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false"
                    aria-controls="collapseEmployee">
                    <i class="bi bi-person-badge pe-nav-icon"></i>
                    <span class="pe-nav-content">Employee</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse" id="collapseEmployee">
                    <li class="pe-slide">
                        <a href="{{url('employe')}}" class="pe-nav-link" data-page="employees">
                            <i class="bi bi-people pe-nav-icon"></i>
                            <span class="pe-nav-content">Employee List</span>
                        </a>
                    </li>
                    <li class="pe-slide">
                        <a href="{{url('payroll')}}" class="pe-nav-link" data-page="employee_payroll">
                            <i class="bi bi-cash-stack pe-nav-icon"></i>
                            <span class="pe-nav-content">Employee Payroll</span>
                        </a>
                    </li>
                </ul>
            </li>   -->
			
           <li class="pe-menu-title">Salary Management</li>

            <li class="pe-slide pe-has-sub">
                <a href="#collapseSalary" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false"
                    aria-controls="collapseSalary">
                    <i class="bi bi-wallet2 pe-nav-icon"></i>
                    <span class="pe-nav-content">Salary Management</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse" id="collapseSalary">

                    <li class="pe-slide">
                        <a href="{{ url('salary_component') }}"   data-page="salary_component" class="pe-nav-link">
                            <i class="bi bi-gear-wide-connected pe-nav-icon"></i>
                            <span class="pe-nav-content">Component</span>
                        </a>
                    </li>

                    <li class="pe-slide">
                        <a href="{{ url('employe_salary_management') }}"  data-page="employe_salary_management" class="pe-nav-link">
                            <i class="bi bi-cash-coin pe-nav-icon"></i>
                            <span class="pe-nav-content">Management</span>
                        </a>
                    </li>

                    <li class="pe-slide">
                        <a href="{{ url('payrol_run_management') }}"  data-page="payrol_run_management" class="pe-nav-link">
                            <i class="bi bi-clock-history pe-nav-icon"></i>
                            <span class="pe-nav-content">Payroll Run</span>
                        </a>
                    </li>

                    <!-- ✅ New Payslip Menu -->
                    <li class="pe-slide">
                        <a href="{{ url('payslip') }}"  data-page="payslip" class="pe-nav-link">
                            <i class="bi bi-file-earmark-text pe-nav-icon"></i>
                            <span class="pe-nav-content">Payslips</span>
                        </a>
                    </li>

                </ul>
            </li>

            <!-- Shifts & Attendance -->
            <li class="pe-menu-title">Shifts & Attendance</li>
            <li class="pe-slide pe-has-sub">
                <a href="#collapseShiftsAttendance" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false"
                    aria-controls="collapseShiftsAttendance">
                    <i class="bi bi-clock-history pe-nav-icon"></i>
                    <span class="pe-nav-content">Shifts & Attendance</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse" id="collapseShiftsAttendance">
                    <li class="pe-slide">
                        <a href="{{url('shifts')}}" data-page="shifts" class="pe-nav-link" data-page="shifts">
                            <i class="bi bi-calendar-week pe-nav-icon"></i>
                            <span class="pe-nav-content">Shifts</span>
                        </a>
                    </li>
                    <li class="pe-slide">
                        <a href="{{url('attendance')}}" class="pe-nav-link" data-page="attendance">
                            <i class="bi bi-people pe-nav-icon"></i>
                            <span class="pe-nav-content">Attendance</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="pe-menu-title">Reports</li>
            <li class="pe-slide pe-has-sub">
                <a href="#collapseReports" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false"
                    aria-controls="collapseReports">
                    <i class="bi bi-graph-up pe-nav-icon"></i>
                    <span class="pe-nav-content">Reports</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse" id="collapseReports">
                    <li class="pe-slide">
                        <a href="{{url('account-statement')}}" data-page="account_report" class="pe-nav-link">
                            <i class="bi bi-card-list pe-nav-icon"></i>
                            <span class="pe-nav-content">Account Report</span>
                        </a>
                    </li>
                    <li class="pe-slide">
                        <a href="{{url('expense-sheet')}}" data-page="expense_sheet" class="pe-nav-link">
                            <i class="bi bi-wallet2 pe-nav-icon"></i>
                            <span class="pe-nav-content">Expense Sheet</span>
                        </a>
                    </li>


                </ul>
            </li>

            <li class="pe-menu-title">Store</li>
            <li class="pe-slide pe-has-sub">
                <a href="#collapseStore" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false"
                    aria-controls="collapseStore">
                    <i class="bi bi-shop pe-nav-icon"></i>
                    <span class="pe-nav-content">Store</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>

                <ul class="pe-slide-menu collapse" id="collapseStore">
                    <li class="pe-slide">
                        <a href="{{url('store-setup')}}" data-page="store_setup" class="pe-nav-link">
                            <i class="bi bi-building pe-nav-icon"></i>
                            <span class="pe-nav-content">Store Setup</span>
                        </a>
                    </li>

                    <li class="pe-slide">
                        <a href="{{url('store-products')}}" data-page="product_setup" class="pe-nav-link">
                            <i class="bi bi-box-seam pe-nav-icon"></i>
                            <span class="pe-nav-content">Product Setup</span>
                        </a>
                    </li>

                    <!-- ✅ NEW: Point of Sale Link -->
                    <li class="pe-slide">
                        <a href="{{url('pos')}}" data-page="pos" class="pe-nav-link">
                            <i class="bi bi-cash-stack pe-nav-icon"></i>
                            <span class="pe-nav-content">POS</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="pe-slide">
                <a href="{{url('permissions')}}" data-page="permissions" class="pe-nav-link">
                    <i class="bi bi-gear pe-nav-icon"></i>
                    <span class="pe-nav-content">Permissions</span>
                </a>
            </li>

        </ul>
    </nav>
</aside>
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>
<script>
document.addEventListener("DOMContentLoaded", () => {
    if ('{{Auth::user()->role}}' == 'employee') {
        const userId = "{{ Auth::user()->id }}";
        fetch(`/api/sidebar/${userId}`)
            .then(res => res.json())
            .then(data => {
                const allowedPages = data.pages || [];
console.log("allowedPages",allowedPages);
                // Hide all menu items by default
                document.querySelectorAll('.pe-nav-link[data-page]').forEach(link => {
                    const page = link.getAttribute('data-page');
                    if (!allowedPages.includes(page)) {
                        link.closest('li').style.display = 'none';
                    }
                });
                // If all child links of a submenu are hidden, hide the parent group
                document.querySelectorAll('.pe-has-sub').forEach(group => {
                    const visibleChild = group.querySelector('li:not([style*="display: none"])');
                    if (!visibleChild) {
                        group.style.display = 'none';
                    }
                });
            })
            .catch(err => console.error('Sidebar load error:', err));
    }
});
</script>