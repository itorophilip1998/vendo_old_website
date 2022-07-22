<div class="navbar-grid font-weight-bold">
	<div class="navbar-logo align-self-center">
		<ul class="nav navbar-nav text-left">
			<a class="navbar-brand" href="./bo_main.php">
				<img src="Images/logo1.png" height="50" alt="">
			</a>
		</ul>
	</div>
	<div class="navbar-items text-center bo_text_header">
		<nav class="navbar navbar-expand navbar-default">
			<ul class="nav justify-content-center align-self-center navbar-nav bo_unordered_list_nav">
				<li class="nav-item mx-auto " id="nav_main">
					<a class="nav-link" href="./bo_main.php"><?php echo localize("bo_navbar_dashboard"); ?></a>
				</li>
				
				<li class="nav-item mx-auto" id="nav_downline">
					<a class="nav-link" href="./bo_downline.php"><?php echo localize("bo_navbar_downline"); ?></a>
				</li>				
				<li class="nav-item mx-auto" id="nav_commission">
					<a class="nav-link" href="./bo_commission.php"><?= localize("bo_navbar_commission") ?></a>
				</li>				
				<?php /*
				<li class="nav-item mx-auto" id="nav_history">
					<a class="nav-link" href="./bo_history.php"><?= localize("bo_navbar_history") ?></a>
				</li>
				<li class="nav-item mx-auto" id="nav_proof">
					<a class="nav-link" href="./bo_proof.php"><?= localize("bo_navbar_proof") ?></a>
				</li>
				*/ ?>
				<li class="nav-item mx-auto" id="nav_vq">
					<a class="nav-link" href="./bo_vq.php"><?= localize("bo_navbar_vq") ?></a>
				</li>				
				<?php
				$show_admin = $user['is_admin'];
				if ($show_admin):
				?>
				<!-- li class="nav-item mx-auto" id="nav_admin">
					<a class="nav-link" href="./admin_pending_bitcoin_transactions.php">Admin</a>
				</li -->	

				<li class="dropdown nav-item mx-auto" id="nav_admin">
					<a href="" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Admin<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="./admin_pending_bitcoin_transactions.php" class="beige"><?php echo(localize("bo_navbar_admin_confirm_payments")); ?></a></li>
						<li><a href="./admin_assign_vqs.php" class="beige"><?php echo(localize("bo_navbar_admin_assign_vqs")); ?></a></li>
						<li><a href="./admin_dashboard.php" class="beige"><?php echo(localize("bo_navbar_admin_dashboard")); ?></a></li>
					</ul>
				</li>

				<?php endif; ?>					
				<?php
				$show_upgrade_button = $user['broker_registration_complete'];
				if ($show_upgrade_button):
				?>
				<li class="nav-item mx-auto" id="nav_upgrade">
					<a class="nav-link" href="./access.php"><?= localize("bo_navbar_upgrade") ?></a>
				</li>	
				<?php endif; ?>			
			</ul>
		</nav>
	</div>
	<div class="navbar-swipe-menu text-right align-self-center beige">
		<div id="mySidenav" class="sidenav sidenav_closed">
			<a href="javascript:void(0)" class="closebtn beige" onclick="closeNav()">&times;</a>
			<div class="d-flex flex-column sidenav_center">
				<div class="">
					<a href="./bo_main.php"><?php echo localize("bo_navbar_dashboard"); ?></a>
					<div class="bo_seperator_sidenav"></div>
				</div>
				<div class="">
					<a href="./bo_profile.php"><?php echo localize("bo_sidebar_settings"); ?></a>
					<div class="bo_seperator_sidenav"></div>
				</div>
				<!-- <div class="">
					<a href="#"><?php echo localize("bo_sidebar_receipts"); ?></a>
					<div class="bo_seperator_sidenav"></div>
				</div>
				<div class="">
					<a href="#"><?php echo localize("bo_sidebar_member_statistic"); ?></a>
					<div class="bo_seperator_sidenav"></div>
				</div>
				<div class="">
					<a href="#"><?php echo localize("bo_sidebar_test_algo"); ?></a>
					<div class="bo_seperator_sidenav"></div>
				</div> -->
				<div class="">
					<a href="bo_terms_and_conditions.php"><?php echo localize("bo_sidebar_term_condition"); ?></a>
					<div class="bo_seperator_sidenav"></div>
				</div>
				<div class="flex-fill">
					<a href="bo_privacy_policy.php"><?php echo localize("bo_sidebar_privacy_policy"); ?></a>
				</div>
				<div class="p-2">
					<a href="./bo_logout.php" class="beige">Log out</a>
				</div>
			</div>


		</div>
		<span style="font-size:30px;cursor:pointer;text-align: right;" onclick="openNav()">&#9776;</span>
	</div>
</div>


<script>
	function openNav() {
		document.getElementById("mySidenav").classList.remove("sidenav_closed");
		document.getElementById("mySidenav").classList.add("sidenav_opened");
	}
	
	function closeNav() {
		document.getElementById("mySidenav").classList.add("sidenav_closed");
		document.getElementById("mySidenav").classList.remove("sidenav_opened");
	}

	function getEventTarget(e) {
		e = e || window.event;
		return e.target || e.srcElement;
	}
</script>
