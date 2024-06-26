<!--sidebar-->
<div class="col-md-3 left_col">
    <div class="left_col scroll-view">

        <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
                <ul class="nav side-menu">
                    <li class="{{ Request::is('tenant/dashboard*') ? 'lead-active' : '' }}" style="margin-top:20px;">
                        <a href="{{ URL::to('tenant/dashboard') }}">
                            <i class="fas fa-chart-line" rel="tooltip" title="Dashboard" ></i>
                            Dashboard
                        </a>

                    </li>

                    <li class="">
                        <a href="{{ URL::to('tenant/lead/lead_type') }}">
                            <i class="fas fa-file-alt" rel="tooltip" title="Lead Type" ></i>
                            Lead Type
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ URL::to('tenant/template') }}">
                            <i class="fas fa-book" rel="tooltip" title="Lead Upload Template"></i>
                            Lead Upload Template
                        </a>

                    </li>

                    <li class="{{ Request::is('tenant/agent*') ? 'lead-active' : '' }}">
                        <a href="{{ URL::to('tenant/agent') }}">
                            <i class="fas fa-address-card" rel="tooltip" title="User Management" ></i>
                            User Management
                        </a>
                    </li>
                    <li class="{{ Request::is('tenant/audit*') ? 'lead-active' : '' }}">
                        <a href="{{ URL::to('tenant/audit') }}" rel="tooltip" title="Audits Management">
                            <i class="fas fa-clipboard-list"></i>
                            Audits Management
                        </a>
                    </li>
                    <li class="{{ !Request::is('tenant/lead/knocks*') && Request::is('tenant/lead*') ? 'lead-active' : '' }}">
                        <a href="{{ URL::to('tenant/lead') }}">
                            <i class="fas fa-users" rel="tooltip" title="Lead Management"></i>
                            Lead Management
                        </a>
                    </li>

                 
                    <li class="{{ Request::is('tenant/followup-lead*') ? 'lead-active' : '' }}">
                        <a href="{{ route('tenant.followup-lead.index') }}">
                            <i class="fas fa-level-up-alt"></i>
                            Follow Up Lead Management
                        </a>
                    </li>
                    <li class="{{ Request::is('tenant/purchase-lead*') ? 'lead-active' : '' }}">
                        <a href="{{ route('tenant.purchase-lead.index') }}">
                            <i class="fas fa-level-up-alt"></i>
                            Purchase Lead Management
                        </a>
                    </li>
                    <li class="{{ Request::is('tenant/deals*') ? 'lead-active' : '' }}">
                        <a href="{{ URL::to('tenant/deals') }}">
                            <i class="fas fa-handshake" rel="tooltip" title="deal Management"></i>
                            Deal Management
                        </a>
                    </li>
                    <li class="{{ Request::is('tenant/marketing*') ? 'lead-active' : '' }}">
                        <a href="{{ route('tenant.marketing.index') }}" rel="tooltip" title="Marketing Management">
                            <i class="fas fa-envelope"></i>
                            Marketing Lead Management
                        </a>
                    </li>
<!--                     <li class="{{ Request::is('tenant/campaign/*') && !Request::is('tenant/campaign/tag*') ? 'lead-active' : '' }}">
                        <a href="{{ route('tenant.campaign.index') }}" rel="tooltip" title="Campaign Management">
                           <i class="fas fa-envelope-open-text"></i>
                            Mailchimp Campaign Management
                        </a>
                    </li>-->
                    <li class="{{ Request::is('tenant/campaign/tag*') ? 'lead-active' : '' }}">
                        <a href="{{ route('tenant.campaign.tag.index') }}" rel="tooltip" title="Campaign Tag">
                           <i class="fas fa-tags"></i>
                            Mailchimp Tag Management
                        </a>
                    </li>
<!--                    <li class="{{ Request::is('tenant/campaign/user*') ? 'lead-active' : '' }}">
                        <a href="{{ route('tenant.campaign.user.index') }}" rel="tooltip" title="Campaign User">
                           <i class="fas fa-tags"></i>
                            Mailchimp User Management
                        </a>
                    </li>-->
                    <li class="{{ Request::is('tenant/follow-status*') ? 'lead-active' : '' }}">
                        <a href="{{ URL::to('tenant/follow-status') }}">
                            <i class="fas fa-info-circle" rel="tooltip" title="Follow Up Lead Status"></i>
                            Follow Up Lead Status
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ URL::to('tenant/lead/lead_status') }}">
                            <i class="fas fa-info-circle" rel="tooltip" title="Lead Status"></i>
                            Lead Status
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ URL::to('tenant/lead/setting/create') }}">
                            <i class="fas fa-cog" rel="tooltip" title="Lead Status"></i>
                            Settings
                        </a>
                    </li>
                    <li class="{{ Request::is('tenant/lead/knocks/user/list*') ? 'lead-active' : '' }}">
                        <a href="{{ URL::to('tenant/lead/knocks/user/list') }}">
                            <i class="fas fa-clipboard-list" rel="tooltip" title="User Knocks List"></i>
                            Knocks List
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ URL::to('tenant/printer_email') }}">
                            <i class="fas fa-envelope-open-text" rel="tooltip" title="Printer Email" ></i>
                            Printer Email
                        </a>

                    </li>
                    <li class="{{ Request::is('tenant/field*') ? 'lead-active' : '' }}">
                        <a href="{{ URL::to('tenant/field') }}">
                            <i class="fas fa-text-width" rel="tooltip" title="Field Management"></i>
                            Field Management
                        </a>
                    </li>

                    <li class="{{ Request::is('tenant/commission*') ? 'lead-active' : '' }}">
                        <a href="{{ URL::to('tenant/commission') }}">
                            <i class="fas fa-dollar-sign" rel="tooltip" title="Commission Management"></i>
                            Commission Management
                        </a>

                    </li>
                    
                    <li class="{{ Request::is('tenant/ommission_event*') ? 'lead-active' : '' }}">
                        <a href="{{ URL::to('tenant/commission_event') }}">
                            <i class="fas fa-wallet" rel="tooltip" title="Commission Event"></i>
                            Commission Event
                        </a>
                    </li>

                    <li class="{{ Request::is('tenant/training*') ? 'lead-active' : '' }}">
                        <a href="{{ URL::to('tenant/training') }}">
                            <i class="fas fa-tasks" rel="tooltip" title="Training Management"></i>
                            Training Management
                        </a>

                    </li>
                    <li class="{{ Request::is('tenant/lead/appoiment_alerts*') ? 'lead-active' : '' }}">
                        <a href="{{ URL::to('tenant/lead/appoiment_alerts') }}">
                            <i class="fas fa-anchor" rel="tooltip" title="Scheduling"></i>
                            Alerts
                        </a>

                    </li>
                    <li class="{{ Request::is('tenant/scheduling*') ? 'lead-active' : '' }}">
                        <a href="{{ URL::to('tenant/scheduling') }}">
                            <i class="far fa-calendar-alt" rel="tooltip" title="Scheduling"></i>
                            Scheduling
                        </a>

                    </li>
                </ul>
            </div>

        </div>
        <!-- /sidebar menu -->

        <!-- /menu footer buttons -->
        {{--<div class="sidebar-footer hidden-small">--}}
            {{--<a data-toggle="tooltip" data-placement="top" title="Settings">--}}
                {{--<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>--}}
            {{--</a>--}}
            {{--<a data-toggle="tooltip" data-placement="top" title="FullScreen">--}}
                {{--<span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>--}}
            {{--</a>--}}
            {{--<a data-toggle="tooltip" data-placement="top" title="Lock">--}}
                {{--<span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>--}}
            {{--</a>--}}
            {{--<a data-toggle="tooltip" data-placement="top" title="Logout" href="login.html">--}}
                {{--<span class="glyphicon glyphicon-off" aria-hidden="true"></span>--}}
            {{--</a>--}}
        {{--</div>--}}
        <!-- /menu footer buttons -->
    </div>
</div>
<!--sidebar-end-->
