
// t3 l photo b Js
function imagePreview(input, selector)
{
    if(input.files && input.files[0])
    {
        var reader = new FileReader();
        reader.onload = function(e)
        {
            $(selector).attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}


//end photo


/*-----------------------------
// Global Variabales
-------------------------------*/

var temporaryMsgId = 0;
var ActiveUsers = [];

const messageForm = $(".message-form"),
      messageInput = $(".message-input"),
      messageBoxContainer = $(".wsus__chat_area_body"),
      messageContactBox =$(".messenger-contacts"),
      galleryBox = $(".gallery-shared"),
      csrf_token = $("meta[name=csrf-token]").attr("content"),
      myid = $("meta[name=auth-id]").attr("content"),
      url = $("meta[name=url]").attr("content");

const getMessengerId = () => $('meta[name=id]').attr("content");
const setMessengerId = (id) => $('meta[name=id]').attr("content", id);

/*-----------------------------
// End Global Variabales
-------------------------------*/

function EnableChatBoxLoader()
{
    $(".wsus__message_paceholder").removeClass('d-none');
}

function DisableChatBoxLoader()
{
    $(".wsus__chat_app").removeClass('show_info');
    $(".wsus__message_paceholder").addClass('d-none');
    $(".wsus__message_paceholder_black").addClass('d-none');

}



/*-----------------------------
// Fetch id data of the user and updaye the view
-------------------------------*/

//let find = 0;
function IDinfo(id)
{


        find = id;
        $.ajax({
        type: "GET",
        url: '/messenger/id-info',
        data: {id: id},
        beforeSend: function()
        {
            NProgress.start();
            EnableChatBoxLoader();
        },
        success: function(data)
        {
            // Fetch Messages
            FetchMessages(data.fetch.id, true);
            Gallery(data.fetch.id,true);

            $(".wsus__chat_info_gallery").html("");
            // if(data?.SharedPhotos)
            // {
            //     $(".nothing_share").addClass('d-none');
            //     $(".wsus__chat_info_gallery").html(data.SharedPhotos);
            // }else{
            //     $(".nothing_share").removeClass('d-none');
            // }
            data.isFavorite ? $(".favourite").addClass('active') : $(".favourite").removeClass('active');
            let avatar= data.fetch.avatar ? data.fetch.avatar : '/Avatars/avatar.png';

            $(".messenger-header").find("img").attr("src", 'storage/' + avatar);
            $(".messenger-header").find("h4").text(data.fetch.name);
            $(".messenger-info-view .user_photo").find("img").attr("src", 'storage/' + avatar);
            $(".messenger-info-view").find(".user_name").text(data.fetch.name);
            $(".messenger-info-view").find(".user_unique_name").text(data.fetch.user_name);
            NProgress.done();

        },
        error: function(xhr, status, error)
        {

            DisableChatBoxLoader();


        }
    });





}



/*-----------------------------
// Send Messages
-------------------------------*/

function SendMessage()
{
    temporaryMsgId ++;
    let tempID = `temp_${temporaryMsgId}`;
    let HasAttachment = !!$(".attachment-input").val();
    const inputValue = messageInput.val();


    if(inputValue.trim() != '' || HasAttachment)
    {
        const formData = new FormData($(".message-form")[0]);
        formData.append("id", getMessengerId());
        formData.append("temporaryMsgId", tempID);
        formData.append("_token", csrf_token);


        $.ajax({
            type: "POST",
            url: '/messenger/send-message',
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,


            beforeSend: function()
            {
                if(HasAttachment)
                {

                    messageBoxContainer.append(sendTempMessageCard(inputValue,tempID,true));
                }else
                {
                    messageBoxContainer.append(sendTempMessageCard(inputValue,tempID));

                }
                $('.no-messages').addClass('d-none');
                ScrollToButtom(messageBoxContainer);
                messageFormReset();
            },

            success: function(data)
            {


                UpdateContactItem(getMessengerId());
                const tempMsgCardElement = messageBoxContainer.find(`.message-card[data-id=${data.tempID}]`);
                tempMsgCardElement.before(data.message);
                tempMsgCardElement.remove();
                initVenobox();


            },

            error: function(xhr, status, error)
            {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(index, value) {
                        notyf.error(value[0]);
                    });

            }


        });
    }

}



function sendTempMessageCard(message ,tempId, attachment = false)
{

    if(attachment)
    {
        return `<div class="wsus__single_chat_area message-card" data-id="${tempId}">
                    <div class="wsus__single_chat chat_right">
                        <div class="pre_loader">
                            <div class="spinner-border text-light" role="status">
                                <span class="visually-hidden">Loading...</span>

                            </div>
                        </div>
                        ${message.trim() != '' ? `<p class="messages">${message}</p>` : ''}

                        <span class="clock"><i class="fas fa-clock"></i> Sending...</span>

                    </div>
                </div>`;
    }else
    {

        return `
                <div class="wsus__single_chat_area message-card" data-id="${tempId}">
                    <div class="wsus__single_chat chat_right">
                        <p class="messages">${message}</p>
                        <span class="clock"><i class="fas fa-clock"></i> Sending...</span>

                    </div>
                </div>`;
    }
}

function reciveMessageCard(e)
{
    if(e.attachment)
    {
        return `<div class="wsus__single_chat_area message-card" data-id="${e.message_id}">
        <div class="wsus__single_chat">
            <a class="venobox" data-gall="gallery${e.id}" href="${'/storage/'+ e.attachment }" >

                <img src="${'/storage/'+ e.attachment }" alt="" class="img-fluid w-100">
            </a>
            ${e.message!= null && e.message.trim() != '' ? `<p class="messages">${e.message}</p>` : ''}

        </div>
    </div>`;
    }else
    {
        return `
        <div class="wsus__single_chat_area message-card" data-id="${e.message_id}">
            <div class="wsus__single_chat">
                <p class="messages">${e.message}</p>

            </div>
        </div>`;
    }
}

/*-----------------------------
// Scroll Function
-------------------------------*/
function actionOnScroll(selector, callback, topScroll = false)
{
    $(selector).on('scroll', function(){
        let element = $(this).get(0);
        const condition = topScroll ? element.scrollTop == 0 : element.scrollTop + element.clientHeight >= element.scrollHeight ;
        if(condition)
        {
            callback();
        }

    });
}



//search function
let searchPage = 1;
let NoMoreDataSearch = false;
let searchTempVal = "";
let setSearchLoading = false;

function searchUsers(query)
{
    if(query != searchTempVal)
    {
        searchPage = 1;
        NoMoreDataSearch = false;
    }
    searchTempVal = query;

    if(!setSearchLoading && !NoMoreDataSearch)
    {

        $.ajax({
            type: "GET",
            url: '/messenger/search',
            data:{query: query, page: searchPage},
            beforeSend: function()
            {
                setSearchLoading = true;

                let loader = `
                    <div class="text-center search-loader">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>`;

                $('.user_search_list_result').append(loader);

            },
            success: function(data)
            {
                setSearchLoading = false;
                $('.user_search_list_result').find('.search-loader').remove();
                if(searchPage < 2)
                {

                    $('.user_search_list_result').html(data.records);
                }else
                {
                    $('.user_search_list_result').append(data.records);
                }

                NoMoreDataSearch = searchPage >= data?.last_page;
                if(!NoMoreDataSearch)  searchPage++;
            },

            error: function(xhr, status, error)
            {
                setSearchLoading = false;
                $('.user_search_list_result').find('.search-loader').remove();


            }

        });
    }
}


function messageFormReset()
{
    $('.attachment-block').addClass('d-none');
    messageForm.trigger("reset");
    var emoji = $('#example1').emojioneArea();
    emoji.data("emojioneArea").setText('');

}




/*-----------------------------
// Fetch Messages
-------------------------------*/
let MessagesPage = 1;
let NoMoreDataMessages = false;
let MessagesLoading = false;
function FetchMessages(id,newFetch= false)
{
    if(newFetch)
    {
        MessagesPage = 1;
        NoMoreDataMessages = false;
    }
    if(!MessagesLoading && !NoMoreDataMessages)
    {

            $.ajax({
                method: "GET",
                url: "/messenger/fetch-messages",
                data: {


                    id: id,
                    page: MessagesPage,

                },

            beforeSend: function()
            {
                MessagesLoading = true;
                let loader = `
                    <div class="text-center messages-loader">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>`;

                messageBoxContainer.prepend(loader);

            },
            success: function(data)
            {
                MessagesLoading = false;
                messageBoxContainer.find('.messages-loader').remove();
                MakeMessageSeen(true);
                if(MessagesPage == 1)
                {
                        messageBoxContainer.html(data.messages);
                        ScrollToButtom(messageBoxContainer);

                }else
                {
                     const LastMsg = $(messageBoxContainer).find(".message-card").first();
                     const CurOffset = LastMsg.offset().top - messageBoxContainer.scrollTop();

                     messageBoxContainer.prepend(data.messages);
                     messageBoxContainer.scrollTop(LastMsg.offset().top - CurOffset);

                }



                //pagination Lock and page increment
                NoMoreDataMessages = MessagesPage >= data?.last_page;
                if(!NoMoreDataMessages)  MessagesPage++;

                DisableChatBoxLoader();
                initVenobox();
            },
            error: function(xhr, status, error)
            {
                MessagesLoading = false;
                messageBoxContainer.find('.messages-loader').remove();

            }





        });

    }

}





/*-----------------------------
// Fetch Contact Messages From Database
-------------------------------*/
let ContactPage = 1;
let NoMoreDataContact = false;
let ContactLoading = false;

function getContacts() {
    if (!ContactLoading && !NoMoreDataContact) {
        $.ajax({
            method: "GET",
            url: "/messenger/fetch-contacts",
            data: { page: ContactPage },
            beforeSend: function()
            {
                ContactLoading = true;
                let loader = `
                    <div class="text-center contact-loader">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>`;
                    messageContactBox.append(loader);
            },
            success: function(data) {

                ContactLoading = false;
                messageContactBox.find('.contact-loader').remove();

                if(ContactPage < 2)
                {

                    messageContactBox.html(data.contacts);
                }else
                {
                    messageContactBox.append(data.contacts);
                }

                NoMoreDataContact = ContactPage >=data?.last_page;
                if(!NoMoreDataContact)  ContactPage ++;

                UpdateUserActiveList();
            },
            error: function(xhr, status, error) {
                ContactLoading = false;
                messageContactBox.find('.contact-loader').remove();


            }
        });
    }
}


/*-----------------------------
// Gallery
-------------------------------*/
let galleryPage = 1;
let NoMoreDataShared = false;
let GalleryLoading = false;

function Gallery(idUser,newFetch= false)
{
    if(newFetch)
        {
            galleryPage = 1;
            NoMoreDataShared = false;
        }
    if(!NoMoreDataShared && !GalleryLoading)
    {
        $.ajax({
            method: "GET",
            url: "/messenger/fetch-shared-gallery",
            data: {
                 id: idUser,
                 page: galleryPage,

             },
             beforeSend: function() {
                GalleryLoading = true;
                let loader = `
                    <div class="text-center  gallery-loader">
                        <div class="spinner-border text-primary " role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>`;

                    galleryBox.append(loader);
            },

            success: function(data) {


                GalleryLoading = false;
                 galleryBox.find('.gallery-loader').remove();


                if(galleryPage < 2 )
                {
                    if(data.SharedPhotos == "")
                    {
                        $(".nothing_share").removeClass('d-none');
                    }else
                    {
                        $(".nothing_share").addClass('d-none');
                    }
                    galleryBox.html(data.SharedPhotos);
                }else
                {
                    galleryBox.append(data.SharedPhotos);
                }


                NoMoreDataShared = galleryPage >=data?.last_page;
                if(!NoMoreDataShared)  galleryPage ++;


            },
            error: function(xhr, status, error) {
                GalleryLoading = false;
                galleryBox.find('.gallery-loader').remove();


            }
        });
    }
}



/*-----------------------------
// Update Contact Item
-------------------------------*/
function UpdateContactItem(user_id)
{
    if(myid != user_id)
    {

        $.ajax({
            method: "GET",
            url: "/messenger/update-contacts/item",
            data: {user_id: user_id},
            beforeSend: function()
            {

            },
            success: function(data)
            {
                messageContactBox.find('.no-contact').remove();
                messageContactBox.find(`.messenger-list-item[data-id="${user_id}"]`).remove();
                messageContactBox.prepend(data.contactItem);

                if(ActiveUsers.includes(+ user_id))
                {
                    userActive(user_id);
                }

                if(user_id == getMessengerId()) UpdateSelectedContent(user_id);

            },
            error: function(xhr, status, error) {

            }

        });
    }
}

function UpdateSelectedContent(UserId)
{
    $('.messenger-list-item').removeClass('active');
    $(`.messenger-list-item[data-id="${UserId}"]`).addClass('active');
}




/*-----------------------------
// Make Message Seen
-------------------------------*/

function MakeMessageSeen(status)
{

    $(`.messenger-list-item[data-id="${getMessengerId()}"]`).find('.unseen_count').hide();

    $.ajax({
        method: "POST",
        url: "/messenger/make-message-seen",
        data: {
            _token: csrf_token,
            id: getMessengerId()
        },

        success: function () {

            $(`.messenger-list-item[data-id="${getMessengerId()}"]`).find('.unseen_count').remove();
        },
        error: function () {}
    })


}


/*-----------------------------
// Favorite
-------------------------------*/

function star(userId)
{
    $(".favourite").toggleClass('active');
    $.ajax({
        method: "POST",
        url: "messenger/make-favorite-user",
        data:
        {
            _token: csrf_token,
            id: userId,

        },
        success: function(data)
        {
            if(data.status === 'User-Added')
            {

                notyf.success('Added To Favorite List .');
            }else
            {
                notyf.success('Removed From Favorite List .');
            }
        },
        error: function(xhr, status, error)
        {

        }

    });
}

/*-----------------------------
// Slide To Buttom On Action
-------------------------------*/

function ScrollToButtom(container)
{
    $(container).stop().animate({
        scrollTop: $(container)[0].scrollHeight
    });
}

// cancel selected attachment
// function cancelAttachment()
// {

// }

/*-----------------------------
// Delete Message Confirmation
-------------------------------*/
function DeleteMessage(MsgId)
{
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            method: "DELETE",
            url: "/messenger/delete-messages",
            data:
            {
                _token: csrf_token,
                msg_id: MsgId,
            },
            beforeSend: function()
            {
                $(`.message-card[data-id="${MsgId}"]`).remove();
            },
            success: function(data)
            {
                UpdateContactItem(getMessengerId());
            },
            error: function(xhr, status, error)
            {

            }

          });
        }
      });
}


function initVenobox() {
    $('.venobox').venobox();
}

//Play Message Sounds

function PlayNotifications()
{
    var audio = new Audio(`/default/8_message-sound.mp3`);
    audio.play();

}




window.Echo.private('message.' + myid).listen('Message', (e) => {
    if (e.event_type === 'delete') {

        if (getMessengerId() == e.from_id) {

            $(`.message-card[data-id="${e.message_id}"]`).remove();
        }
        UpdateContactItem(e.from_id);


    } else if (e.event_type === 'message') {

        if (getMessengerId() != e.from_id) {
            UpdateContactItem(e.from_id);
            PlayNotifications();
        }
        let Message = reciveMessageCard(e);
        if (getMessengerId() == e.from_id) {
            messageBoxContainer.append(Message);
            initVenobox();
            ScrollToButtom(messageBoxContainer);
            MakeMessageSeen(true);
            UpdateContactItem(e.from_id);
        }
    }
});







//Listen To Online Channel

window.Echo.join('online')

    .here((users) => {


         setActiveUsers(users)
        $.each(users, function(index, user){
            userActive(user.id);
        });
    })

    .joining((user) => {
        NewActiveUser(user.id);
        userActive(user.id);
    })

    .leaving((user) => {

        OfflineUser(user.id);
        userInActive(user.id);
    });


    function UpdateUserActiveList()
    {
        $(`.messenger-list-item`).each(function(index,value)
        {
            let id = $(this).data('id')
            if(ActiveUsers.includes(id))  userActive(id);

        });
    }

    function setActiveUsers(users)
    {
        $.each(users, function(index,user)
        {
            ActiveUsers.push(user.id);
        });
    }

    function NewActiveUser(id)
    {
        ActiveUsers.push(id);
    }

    function OfflineUser(id)
    {
        let index = ActiveUsers.indexOf(id);

        if(index != -1)
        {
            ActiveUsers.splice(index, 1);
        }
    }


    function userInActive(id)
    {
        let contactItem = $(`.messenger-list-item[data-id="${id}"]`).find('.img').find('span');
        contactItem.removeClass('active');
        contactItem.addClass('inactive');
    }


    function userActive(id)
    {
        let contactItem = $(`.messenger-list-item[data-id="${id}"]`).find('.img').find('span');
        contactItem.removeClass('inactive');
        contactItem.addClass('active');
    }



//On Doom Load

$(document).ready(function(){


    getContacts();

    if(window.innerHeight < 900)
    {
        $("body").on('click', '.messenger-list-item', function()
        {
            $(".wsus__user_list").addClass('d-none');

        });

        $("body").on('click', '.back_to_list', function()
        {
            $(".wsus__user_list").removeClass('d-none');

        });


    }

    //ki t selecter photo tet7att
    $('#select_file').on('change', function(){
        imagePreview(this, $('.preview'));
    });

    // t9lil a request li bla fayda
    //search user
    function debounce(callback, delay)
    {
        let timerId ;
        return function(...args)
        {
            clearTimeout(timerId);
            timerId = setTimeout(() =>{
                callback.apply(this, args)
            }, delay);
        }
    }

    const debouncedSearch = debounce(function(){
        const value = $('.user_search').val();
        searchUsers(value);
    },500);// ms

    // search action on keyup

    $('.user_search').on('keyup', function(){
        let query = $(this).val();
        if($.trim(query) !== '')
        {
            debouncedSearch();
        }

    });
    //end search user


    //search pagination
    actionOnScroll(".user_search_list_result", function(){
        let value = $('.user_search').val();
        searchUsers(value);
    });




    //Click action for messenger list item

    $("body").on("click", ".messenger-list-item",function(){
        const dataId = $(this).attr('data-id');
        UpdateSelectedContent(dataId);
        setMessengerId(dataId);
        IDinfo(dataId);
        messageFormReset();
    });


    //Send Message

    $(".message-form").on("submit", function(e){
        e.preventDefault();
        SendMessage();

    });


    //send attachment
    $('.attachment-input').on('change', function(){
        imagePreview(this, $('.attachment-preview'));
        $('.attachment-block').removeClass('d-none');
    });

    $(".cancel-attachment").on('click', function(){
        messageFormReset();
    });

    //message Pagination

    actionOnScroll(".wsus__chat_area_body", function(){
        FetchMessages(getMessengerId());
    },true);

    actionOnScroll(".messenger-contacts", function(){
        getContacts();
    });

    actionOnScroll(".wsus__chat_info_gallery", function(){
        Gallery(getMessengerId());
    });



    //Add / Remove To Favorite
    $(".favourite").on('click', function(e){
        e.preventDefault();
        star(getMessengerId());
    });

    $("body").on('click', '.dlt-message', function(e){
        e.preventDefault();
        let id = $(this).attr('data-id');
        DeleteMessage(id);

    });


        // custom hight adjustment
        function adjustHeight() {
            var windowHeight = $(window).height();
            $('.wsus__chat_area_body').css('height', (windowHeight-120) + 'px');
            $('.messenger-contacts').css('max-height', (windowHeight - 393) + 'px');
            $('.wsus__chat_info_gallery').css('height', (windowHeight - 355) + 'px !imprtant');
            $('.user_search_list_result').css({
                'height': (windowHeight - 130) + 'px',
            });


        }

        // Call the function initially
        adjustHeight();

        // Call the function whenever the window is resized
        $(window).resize(function () {
            adjustHeight();
        });


});




