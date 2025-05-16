$(function() {

  Pusher.logToConsole = true;

  var pusher = new Pusher(window.PUSHER_APP_KEY, {
      cluster: window.PUSHER_APP_CLUSTER,
      authEndpoint: '/broadcasting/auth',
      auth: {
          headers: {
            'X-CSRF-TOKEN': window.CSRF_TOKEN
          }
      }
  });

  var channel = pusher.subscribe('presence-online-users');

  let userIndex = 1;

  channel.bind('pusher:subscription_succeeded', function(members) {
    $('#online-users-table tbody').empty();
    userIndex = 1;
    members.each(function(member) {
      addUserRow(member);
    });
  });

  channel.bind('pusher:member_added', function(member) {
    addUserRow(member);
  });

  channel.bind('pusher:member_removed', function(member) {
    removeUserRow(member);
  });

  function addUserRow(member) {
    if ($('#user-' + member.id).length === 0) {
      let lastSeen = member.info.last_seen ? member.info.last_seen : 'N/A';
      let ipAddress = member.info.ip_address || 'N/A';
      let email = member.info.email || 'N/A';
      let avatarUrl = member.info.avatar && member.info.avatar.trim() !== ''
      ? member.info.avatar
      : `https://ui-avatars.com/api/?name=${encodeURIComponent(member.info.name)}&background=random&color=fff&size=40`;

      let row = `
        <tr id="user-${member.id}">
          <td>${userIndex++}</td>
          <td class="productimgname">
            <a href="#" class="product-img">
              <img src="${avatarUrl}" alt="avatar">
            </a>
            <a href="#">${member.info.name}</a>
          </td>
          <td>${email}</td>
          <td>${lastSeen}</td>
          <td>${ipAddress}</td>
          <td><span class="badge bg-success">Online</span></td>
        </tr>
      `;
      $('#online-users-table tbody').append(row);
    }
  }

  function removeUserRow(member) {
    $('#user-' + member.id).remove();
    // Recalculate Sno
    $('#online-users-table tbody tr').each(function(index) {
      $(this).find('td:first').text(index + 1);
    });
    userIndex = $('#online-users-table tbody tr').length + 1;
  }
});
