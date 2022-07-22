<?php

include_once("translate.php");
include_once("configuration.php");
include_once("utils.php");
include_once("enums.php");


$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

include 'bo_read_user_logged_in.php';
// is logged in

try {

    $sponsorTree_ = loadSponsorTree($pdo, $user["id"], false, 1);

    $htmlTableRows = createDownlineHtmlTableRows($sponsorTree_, $user["downline_level"]);

} catch (Exception $e) {
    $msg = "exception: " . $e->getMessage();
    error_log($msg);
}

?>

<html>

<head>
    

    <?php
    echo file_get_contents("bo_head.html");
    ?>
</head>

<body>

    <div id="divToBlur" class="bo_container_all">

        <!-- navbar -->
        <div>
            <?php
            include("bo_navbar.php");
            ?>
        </div>

        <div class="bo_container_content bo_container_content_dl main-color">

            <div class="bo_dl_container_summary">
                <div class="bo_panel">
                    <div class="row bo_panel_title flex-fill">
						<div class="col">
                        <?php echo localize("bo_stats_members"); ?>
						</div>
						<div class="col text-right">
							<!-- div class="dropdown">
								<button class="btn btn-secondary dropdown-toggle dropdownBackground dropdownButton bo_text_normal" type="button" id="filterMembers" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									All time
								</button>
								<div class="dropdown-menu" aria-labelledby="filterMembers">
									<a class="dropdown-item" href="#">Action</a>
									<a class="dropdown-item" href="#">Another action</a>
									<a class="dropdown-item" href="#">Something else here</a>
								</div>
							</div -->
						</div>
					</div>

                    <div class="row flex-fill">
						<div class="col">
							<div class="bo_text_normal info_label" ><?php echo localize("bo_stats_direct_members"); ?></div>
							<div class="bo_text_big">&nbsp;<?php echo $sponsorTree_["data"]["downline_direct_count"] ?></div>
						</div>
					</div>
					<div class="bo_separator"></div>
					<div class="row flex-fill">
						<div class="col">
						<div class="bo_text_normal info_label" ><?php echo localize("bo_stats_total_members"); ?></div>
						<div class="bo_text_big">&nbsp;<?php echo $sponsorTree_["data"]["downline_total_count"] ?></div>
						</div>
					</div>

                </div>
            </div>

            <div class="bo_dl_container_downline">
                <div class="bo_panel" style="min-height: 600px;">
                    <div class="row bo_panel_title">
                        <div class="col">
                            Downline
                        </div>
                    </div>

					<div class="row bo_dl_table_members">
						<div class="col">
							<table class="table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent;">
								<thead class="bo_text_header table_bottom_line">
									<th class="fontBold">
										<?php echo localize("bo_dl_table_level_nav"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_dl_table_level"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_dl_table_partner"); ?>
									</th>
									<th class="fontBold">
										<span class="full-text"><?php echo localize("bo_dl_table_direct_members"); ?></span><span class="short-text"><?php echo localize("bo_dl_table_direct"); ?></span>
									</th>
									<th class="fontBold">
                                        <span class="full-text"><?php echo localize("bo_dl_table_total_members"); ?></span><span class="short-text"><?php echo localize("bo_dl_table_total"); ?></span>
                                    </th>
                                    <th class="fontBold">
										<?php echo localize("bo_dl_table_access_volume"); ?>
									</th>
								</thead>
								<tbody id="tableDownlineBody" class="bo_text_table_body">
                                    <?php echo $htmlTableRows ?>
								</tbody>
							</table>
						</div>
					</div>

                </div>
            </div>

        </div>
    </div>


    <script>
        window.onload = function() {
            document.getElementById("nav_downline").classList.add("active");
        }

        $(document).on("click", ".row_table_downline", 
			async function(event){
                var targetRow = event.target.parentNode;
                var isOpen = targetRow.getAttribute("open");
                var numMembersDirect = parseInt(targetRow.getAttribute("data-num-direct"));
                var userId = targetRow.getAttribute("data-user-id");
                var level = targetRow.getAttribute("data-level");

                if(numMembersDirect > 0) {
                    if(isOpen != "true") {
                        var text = await fetchHtmlAsText("ajax_get_downline_table_rows.php?userId=" + userId);

                        if (targetRow.insertAdjacentHTML)
                        {
                            $(text).insertAfter(targetRow).hide().slideDown();
                            //targetRow.insertAdjacentHTML ("afterend", text)
                        }
                        else {
                            var range = document.createRange();
                            var frag = range.createContextualFragment(text);
                            targetRow.parentNode.insertBefore(frag, targetRow.nextSibling);
                        }

                        targetRow.setAttribute("open", "true");
                        //$(this).siblings(".downLineInline").children(".downLineContainer").children(".showDLChildren").html(text);
                        targetRow.childNodes[0].innerHTML = "<i class=\"fas fa-chevron-down\"></i>";
                    } else {
                        var nextRow = targetRow.nextSibling;
                        while(nextRow.nodeType == Node.TEXT_NODE)
                            nextRow = nextRow.nextSibling;
                        var nextRowLevel = parseInt(nextRow.getAttribute("data-level"));
                        while (nextRow && nextRowLevel > level) {
                            nextRow_ = nextRow.nextSibling;
                            while(nextRow_.nodeType == Node.TEXT_NODE)
                                nextRow_ = nextRow_.nextSibling;
                            $(nextRow).slideUp();
                            nextRow.remove();
                            nextRow = nextRow_;
                            nextRowLevel = parseInt(nextRow.getAttribute("data-level"));
                        }
                        targetRow.setAttribute("open", "false");
                        targetRow.childNodes[0].innerHTML = "<i class=\"fas fa-chevron-right\"></i>";
                    }
                }
			}
		);

        async function fetchHtmlAsText(url) {
            const response = await fetch(url);
            if (response.ok)
            {
                return await response.text();
            }
            else
            {
                if (response.status == 401)
                {
                    //session timeout - redirect to login
                    window.location.href = "/bo_login.php?destination=%2Fbo_downline.php";
                }
                else
                {
                    return "";
                }
            }
        }

        $(document).on("click", ".downLineSummary",
            async function(event) {
                if (!$(this).parent("details")[0].hasAttribute("open")) {
                    var text = await fetchHtmlAsText("ajax_get_subtree.php?userId=" + $(this).data("userid"));
                    $(this).siblings(".downLineInline").children(".downLineContainer").children(".showDLChildren").html(text);
                }
            }
        );
        // document.addEventListener("onclick", someClick(event));

        // showButtonCloseAll(false);

        function someClick(event) {

            if (event.target.parentNode.tagName == "DETAILS") {
                var nodeDetails = event.target.parentNode;

                var doShowButton = false;

                if (!nodeDetails.hasAttribute("open")) {
                    doShowButton = true;
                } else {
                    var listTagsDetails = document.getElementsByTagName("DETAILS");

                    for (var i = 0; i < listTagsDetails.length; i++) {
                        if (listTagsDetails[i] != nodeDetails &&
                            listTagsDetails[i].hasAttribute("open")) {
                            doShowButton = true;
                        }
                    }
                }

                //showButtonCloseAll(doShowButton);
            }
        }


        function showButtonCloseAll(doShow) {
            var x = document.getElementById("button-close-tree");

            if (doShow) {
                x.style.display = "inline";
            } else {
                x.style.display = "none";
            }
        }


        function closeTree() {
            var listTagsDetails = document.getElementsByTagName("details");
            for (var i = 0; i < listTagsDetails.length; i++) {
                listTagsDetails[i].removeAttribute("open");
            }

            showButtonCloseAll(false);
        }
    </script>

</body>

</html>