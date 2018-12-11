function addDataToList(data) {
    var scoreList = $('#scoresList');
    scoreList.empty();
    for (var i = 0; i < data.length; i++) {
        var element = data[i];
        scoreList.append('<tr><td>'+element.rank+'</td><td>' + element.username + '</td><td>'+element.time+'</td></tr>');
    }
}

function selectChange(select) {
    $.ajax('score/top.php?levelId=' + select.value,
        {
            success: addDataToList,
            error: function() {
                console.log("failure");
            }
        }
    );
}

function getContext() {
    var level = $('#levelSelect').val();
    var email = $('#emailText').val();
    $.ajax('score/context.php?levelId=' + level + '&email=' + email, 
        {
            success: addDataToList,
            error: function() {
                console.log("Request failed");
            }
        }
    );
}

$(function() {
    if (typeof $('#emailSelect') == 'undefined') {
        var scoreList = $('#levelSelect');
        selectChange({value: scoreList.val()});
    }
});