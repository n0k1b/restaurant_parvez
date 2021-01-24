<aside id="ms-side-nav" class="side-nav fixed ms-aside-scrollable ms-aside-left">
    <!-- Logo -->
    <div class="logo-sn ms-d-block-lg">
      <a class="pl-0 ml-0 text-center" href="index-2.html">
        <img src="" alt="logo">
      </a>
    </div>
    <!-- Navigation -->
    <ul class="accordion ms-main-aside fs-14" id="side-nav-accordion">

      <!-- product -->

      <!-- product end -->
      <!-- orders -->
      <li class="menu-item">
        <a href="{{route('show_all_category')}}"> <span><i class="fas fa-clipboard-list fs-16"></i>Category</span>
        </a>
      </li>

      <li class="menu-item">
        <a href="{{route('show_all_table_id')}}"> <span><i class="fas fa-clipboard-list fs-16"></i>Table Unique id</span>
        </a>
      </li>

      <li class="menu-item">
        <a href="{{route('show_all_menu')}}"> <span><i class="fas fa-clipboard-list fs-16"></i>Menu</span>
        </a>
      </li>

      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#customer" aria-expanded="false" aria-controls="customer"> <span><i class="fas fa-user-friends fs-16"></i>Order </span>
        </a>
        <ul id="customer" class="collapse" aria-labelledby="customer" data-parent="#side-nav-accordion">
          <li> <a href="{{ route('show_all_new_order') }}">New Order</a>
          </li>
          <li> <a href="{{ route('show_all_order') }}">All Order</a>
          </li>

        </li>
        </ul>
      </li>

      <li class="menu-item">
        <a href="{{route('show_all_expense')}}"> <span><i class="fas fa-clipboard-list fs-16"></i>Expense</span>
        </a>
      </li>

      <li class="menu-item">
        <a href="{{route('show_report_menu')}}"> <span><i class="fas fa-clipboard-list fs-16"></i>Report</span>
        </a>
      </li>



      <!-- orders end -->
      <!-- restaurants -->

      <!-- /Basic UI Elements -->
      <!-- Advanced UI Elements -->

      <!-- /Apps -->
    </ul>
  </aside>
