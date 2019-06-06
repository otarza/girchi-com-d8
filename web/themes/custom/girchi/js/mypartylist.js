$(document).ready(function () {


    $('.bs-searchbox :input').on('keyup', (e) => {
        let keyword = e.target.value;
        $.ajax({
            type: "GET",
            url: "/getusers?user=" + keyword,
        })
            .done((data) => {
                $(`<option
                value="3"
                data-content='
            <div class="d-flex w-100 align-items-center p-1">
            <span
                class="rounded-circle overflow-hidden d-inline-block"
            >
                <img
                src="images/i.metreveli.jpg"
                width="35"
                class="rounded pl-politician-avatar"
                alt="..."
                />
            </span>
            <h6
                class="text-uppercase line-height-1-2 font-size-3 font-size-xl-3 mb-0 mx-2"
            >
                <span
                class="text-decoration-none d-inline-block text-hover-success"
                >
                <span class="pl-politician-first-name">ილია</span>
                <span class="font-weight-bold pl-politician-last-name">ნოზახე</span>
                </span>
                <span class="d-flex font-size-1 text-grey pl-politician-position">
                ტესტ
                </span>
            </h6>
            '
            ></option>`).appendTo('#politician');
                $("#politician").selectpicker("refresh");
            });
    })
});