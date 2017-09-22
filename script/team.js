$(function () {

    // Load Modal

    $(document).on('click', '.team-create', function(){
        $('#teamModifyModal').find('form')[0].reset();

        $('#teamModifyModal').find('form').attr('id', 'teamCreateForm');
        $('#teamModifyModal').find('.team-member-list').find('span.label').remove();
        $('#teamModifyModal').find('.team-member-list').siblings('input').remove();
    });
    
    
    $(document).on('click', '.team-edit', function() {
        $('#teamModifyModal').find('form')[0].reset();

        $('#teamModifyModal').find('.team-member-list').find('span.label').remove();
        $('#teamModifyModal').find('.team-member-list').siblings('input').remove();

        $(document).getTeam($(this).attr('data-value')).done(function(data) {
            $('#teamModifyModal').find('form').attr('data-value', data['id']);
            $('#teamModifyModal').find('form').attr('id', 'teamUpdateForm');

            $('#teamModifyModal').find('[name="name"]').val(data['name']);
            $(document).displayMember(data['members'], true);
        });
    });
    
    // Team Member

    $('.team-member').keypress(function (e) {
        if(e.which == 13 || e.which == 32) {
            var result = $(document).validateMember($(this).val().toLowerCase());
            
            if(result['exist']) {
                if(!$(this).closest('.team-member-list').parent().has(`input[name="members[]"][value="${$(this).val().toLowerCase()}"]`).length){

                    $(this).before(
                        `<span class="label label-default">${result['first_name']} ${result['last_name']} <a class="team-member-remove" data-value="${$(this).val().toLowerCase()}">&times;</a></span>`
                    );

                    $(this).closest('.team-member-list').parent().append(
                        `<input type="hidden" name="members[]" value="${$(this).val().toLowerCase()}" />`
                    );
                }
            } else {
                alert('User does not exist in the company');
            }

            $(this).val('');
            return false;
        }
    });


    $(document).on('click', '.team-member-remove', function() {
        $(this).closest('.team-member-list').parent().find(`input[name="members[]"][value="${$(this).attr('data-value')}"]`).remove();
        $(this).parent().remove();
    });

    // Submit

    $(document).on('click', '#teamSubmit', function () {
        var team = $(this).closest('form').serializeArray();
        
        if($(this).closest('form').attr('id') == 'teamCreateForm')
            $(document).postTeam(team);
        else if($(this).closest('form').attr('id') == 'teamUpdateForm')
            $(document).postTeam(team, $(this).closest('form').attr('data-value'));
    });
});