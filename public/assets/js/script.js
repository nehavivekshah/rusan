// Fallback for Android integration function to prevent ReferenceErrors on non-login pages
window.loadSharedPrefData = window.loadSharedPrefData || function() {};

function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.querySelector(".sidebar");
    if (!sidebar) return;

    const closeBtn = document.querySelector("#btn");
    const closemBtn = document.querySelector("#mbtn");

    // Check if the sidebar state is saved in cookies
    const sidebarState = getCookie("sidebarOpen");

    let screenWidth = window.innerWidth;

    // Apply the saved state (open or closed) from cookies
    if (sidebarState === "open" && screenWidth >= 992) {
        sidebar.classList.add("open");
        if (closeBtn) closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");
    } else {
        sidebar.classList.remove("open");
        if (closeBtn) closeBtn.classList.replace("bx-menu-alt-right", "bx-menu");
    }
    
    const toggleTriggers = document.querySelectorAll(".sidebar-toggle-trigger");

    // Create overlay if it doesn't exist
    let overlay = document.querySelector(".sidebar-overlay");
    if (!overlay) {
        overlay = document.createElement("div");
        overlay.className = "sidebar-overlay";
        document.body.appendChild(overlay);
    }

    // Toggle menu
    function toggleSidebar() {
        const isOpen = sidebar.classList.toggle("open");
        setCookie("sidebarOpen", isOpen ? "open" : "closed", 30);
        
        // Handle overlay on mobile
        if (window.innerWidth < 992) {
            if (isOpen) {
                overlay.classList.add("active");
                document.body.style.overflow = "hidden";
            } else {
                overlay.classList.remove("active");
                document.body.style.overflow = "";
            }
        }
    }

    // Attach to ALL triggers
    if (closeBtn) closeBtn.onclick = toggleSidebar;
    if (closemBtn) closemBtn.onclick = toggleSidebar;
    
    document.querySelectorAll(".sidebar-toggle-trigger").forEach(el => {
        el.onclick = toggleSidebar;
    });
    
    // Close sidebar on overlay click (mobile)
    overlay.onclick = function() {
        sidebar.classList.remove("open");
        overlay.classList.remove("active");
        document.body.style.overflow = "";
        setCookie("sidebarOpen", "closed", 30);
    };

    // Auto-close sidebar on link click for mobile
    const navLinks = sidebar.querySelectorAll(".nav-list a");
    navLinks.forEach(link => {
        link.addEventListener("click", () => {
            if (window.innerWidth < 992) {
                sidebar.classList.remove("open");
                overlay.classList.remove("active");
                document.body.style.overflow = "";
            }
        });
    });

    function menuBtnChange() {
        if (!closeBtn) return;
        if (sidebar.classList.contains("open")) {
            closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");
        } else {
            closeBtn.classList.replace("bx-menu-alt-right", "bx-menu");
        }
    }

    function saveSidebarState() {
        if (sidebar.classList.contains("open")) {
            setCookie("sidebarOpen", "open", 1); // Save the 'open' state for 7 days
        } else {
            setCookie("sidebarOpen", "closed", 1); // Save the 'closed' state for 7 days
        }
    }
});

function addtask(id){
    document.querySelectorAll('.task-form').forEach(function(el) {
       el.style.display = 'none';
    });
    document.querySelector("#tf"+id).style = 'display:block!important;';
}

$(document).ready(function(){
    
    $('#tasktitle').keyup(function(e){
        e.preventDefault();
        var tasktitle = $('#tasktitle').val();
        var taskid = $('#taskid').val();
        //var data = $('#form-data').serialize();
        //alert(tasktitle);
        /*headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },*/
        $.ajax({
            type: 'get',
            url: "/tasksubmit",
            data: {taskid:taskid,tasktitle:tasktitle},
            
            beforeSend: function(){
                //alert('....Please wait');
            },
            success: function(response){
                //alert(response);
                //console.log(response);
            },
            complete: function(response){
                //alert(response);
                //console.log(response);
            }
        });
    });
    
    $(document).on('submit', '#edttaskdetails', function (e) {
        
        e.preventDefault();
        var formData = new FormData(this);
        
        $.ajax({
            url: '/tasksubmit',
            type: 'POST',
            data: formData,
            beforeSend: function(){
                //alert('....Please wait');
                $('#res').html('....Please wait');
                $('#res').css('color','#ff7b00');
            },
            success: function (response) {
                // handle success response
                //console.log(response.data);
                $('#res').html(response.success);
                $('#res').css('color','#008000');
            },
            error: function (response) {
                // handle error response
                //console.log(response.data);
                $('#res').html(response.success);
                $('#res').css('color','#f44336');
            },
            contentType: false,
            processData: false
        });
        
    });
    
    $(document).on('submit', '#taskComments', function (e) {
        
        e.preventDefault();
        var formData = new FormData(this);
        
        $.ajax({
            url: '/tasksubmit',
            type: 'POST',
            data: formData,
            beforeSend: function(){
                //alert('....Please wait');
                $('#res1').html('....Please wait');
                $('#res1').css('color','#ff7b00');
            },
            success: function (response) {
                if (response.success === 'Submitted') {
                    $('#res1').html('✓ Posted');
                    $('#commentInputs').val('');
                    $('#reloadMsg').html(response.message);
                    $('#res1').css('color','#008000');
                    setTimeout(() => $('#res1').html(''), 2000);
                } else {
                    $('#res1').html(response.success || 'Error');
                    $('#res1').css('color','#f44336');
                }
            },
            error: function (response) {
                // handle error response
                //console.log(response.data);
                $('#res1').html(response.success);
                $('#reloadMsg').html(response.message);
                $('#res1').css('color','#f44336');
            },
            contentType: false,
            processData: false
        });
        
    });
    

    
	$(document).on("click", ".taskstart", function(e){
	    let ele = $(this);
	    var tskstartId = ele.attr('id');
	    var tskhr = ele.attr('data-taskhr');
	    //alert(tskhr);
	    
	    $.ajax({
            type: 'get',
            url: "/tasksubmit",
            data: {tskstartId:tskstartId,tskhr:tskhr},
            
            beforeSend: function(){
                ele.html('<i class="bx bx-loader"></i> <span>Loading..</span>');
            },
            success: function(response){
                // Determine the correct task ID to refresh (prioritize data-taskid)
                const refreshId = ele.data('taskid') || tskstartId;

                // Refresh the modal to show the new timer state
                if (typeof refreshTaskDetails === 'function') {
                    refreshTaskDetails(refreshId);
                } else {
                    // Fallback to text update if refresh fails
                    ele.html('<i class="bx bx-check"></i> <span class="ms-1">Saved</span>');
                }
            }
        });
	});
    
    $(document).on("click", ".taskdeleted", function(e){
	    const ele = $(this);
	    const deltaskid = ele.attr("id");
	    //alert(deltaskid);
	    
	    $.ajax({
            type: 'get',
            url: "/tasksubmit",
            data: {deltaskid:deltaskid},
            
            beforeSend: function(){
                ele.html('<i class="bx bx-loader"></i> <span>Loading..</span>');
            },
            success: function(response){
                // Remove task card without page refresh
                $(".tk-card[data-taskid='" + deltaskid + "']").remove();
                if(typeof closeTaskAjax === "function") { closeTaskAjax(); }
                // window.location.href="/task";
            },
            complete: function(response){
                //alert("Complete");
                //ele.html('<i class="bx bx-play"></i> <span class="p-0">Start</span>');
            }
        });
	});
    
});