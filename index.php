<?php
session_start();
if(!isset($_SESSION['email'])) {
  header("Location: login.php");
}
?>
<html>
<head>
  <script src="jquery-3.6.3.min.js"></script>
  <script src="jquery.ui/jquery-ui.min.js"></script>
  <script src="jquery.tablesorter/js/jquery.tablesorter.js"></script>
  <script src="jquery.tablesorter/js/jquery.tablesorter.widgets.js"></script>
  <script src="jquery.tablesorter/js/widgets/widget-pager.min.js"></script>
  <script src="jquery.tablesorter/js/widgets/widget-filter-formatter-html5.min.js"></script>
  <script src="jquery.tablesorter/js/widgets/widget-filter-formatter-jui.min.js"></script>
  <script src="jquery.tablesorter/js/widgets/widget-formatter.min.js"></script>
  <link rel="stylesheet" href="jquery.ui/jquery-ui.min.css">
  <link rel="stylesheet" href="jquery.tablesorter/css/theme.default.min.css"></script>
  <link rel="stylesheet" href="jquery.tablesorter/css/theme.blue.css"></script>
  <link rel="stylesheet" href="jquery.tablesorter/css/jquery.tablesorter.pager.min.css"></script>
  <link rel="stylesheet" href="jquery.tablesorter/css/filter.formatter.min.css">
  <link rel="stylesheet" href="css/common.css">
  <link rel="stylesheet" href="css/index.css">
</head>
<body>
  <div id="logout">
    <a href="logout.php">Logout</a>
  </div>
  <div id="forwarder-form">
    <form method="post" action="">
      <div id="forwarder-form-container">
        <p>
          <label for="forwarder-email">Forwarder:</label>
          <input type="email" id="forwarder-email" placeholder="Enter New Forwarder" name="forwarder">
        </p>
        <p>
          <label for="forwarder-expiration">Expiration Date:</label>
          <input type="date" id="forwarder-expiration" placeholder="Enter Date" name="expiration">
          <input type="checkbox" id="forwarder-eternal" placeholder="Never" name="forward-eternal">
          <label id="forwarder-eternal-label">
            Never
          </label>
        </p>
        <p class="submit">
          <button type="submit">Create</button>
        </p>
      </div>
    </form>
    <p id="forwarder-errors">
    </p>
  </div>
  <div id="forwarder-data">
    <table id="forwarder-data-table">
      <thead>
        <tr>
          <th class="col-email col-forwarder">Forwarder</th>
          <th class="col-email col-destination" data-value="<?php echo $_SESSION['email']; ?>">Destination</th>
          <th class="sorter-shortDate col-date" data-date-format="yyymmdd">Expiration Date</th>
          <th data-filter="false" data-sorter="false"></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <!-- pager -->
    <div id="pager" class="pager">
      <form>
        <img src="jquery.tablesorter/css/images/first.png" class="first"/>
        <img src="jquery.tablesorter/css/images/prev.png" class="prev"/>
        <span class="pagedisplay"></span> <!-- this can be any element, including an input -->
        <img src="jquery.tablesorter/css/images/next.png" class="next"/>
        <img src="jquery.tablesorter/css/images/last.png" class="last"/>
        <select class="pagesize">
          <option selected="selected" value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
      </form>
    </div>
  </div>
  <script type="application/javascript">
    const LOGIN_EMAIL = "<?php echo $_SESSION['email']; ?>";
    $(document).ready(function() {
      // Initialize form.
      $('#forwarder-eternal').change((e) => {
        if($(e.target).prop('checked')) {
          $('#forwarder-expiration')
            .attr('data-originalValue', $('#forwarder-expiration').val())
            .val('')
            .css({'color':'transparent'})
            .prop('disabled', true);
        } else {
          $('#forwarder-expiration')
            .val($('#forwarder-expiration').attr('data-originalValue'))
            .attr('type','date')
            .attr('data-originalValue', '')
            .css({'color':''})
            .prop('disabled', false);
        }
      });
      $('input').focus((e) => {
        $(e.target).parent().children('.error').remove()
      });
      function reset_expiration() {
        $('#forwarder-eternal').prop('checked', false);
        $('#forwarder-eternal').trigger('change');
        $('#forwarder-expiration').val(timestamp_to_date(Date.now()/1000 + 24*60*60));
      }
      reset_expiration();

      function timestamp_to_date(timestamp) {
        var date = new Date(timestamp*1000);
        if(isNaN(date)) return "N/A";
        var day = ("00" + date.getDate()).slice(-2);
        var month = ("00" + (date.getMonth()+1)).slice(-2);
        //return date.toISOString().slice(0,10);
        return date.getFullYear() + "-" + month + "-" + day;
      }

      function delete_row($row, forwarder, destination, expiration) {
        var response = $.post({
          url: 'delete.php',
          data: {
            forwarder: forwarder,
            destination: destination,
            expiration: expiration,
          },
          dataType: 'json'
        }).done((data) => {
          if(data.error) {
            $('#forwarder-errors').append(`<span class="error">${data.error}</span>`);
            $('body').animate({
              scrollTop: 0
            });
          } else {
            $row.remove();
            $('#forwarder-data-table').trigger('update');
          }
        }).fail((data) => {
          $('#forwarder-errors').append(`<span class="error">Unknown error</span>`);
          console.error(data);
        });
      }

      function add_forwarder(row) {
        var $row = $('<tr><td>'+row.forwarder+'</td>'+
          '<td>'+row.destination+'</td>'+
          '<td>'+row.expiration+'</td><td></td></tr>');
        if(row.destination !== LOGIN_EMAIL) {
          $row.addClass('not-owned');
        }
        var $link = $('<a href="#" class="remove">\u2718</a>');
        $row.find('td').last().append($link);
        $link.click(function(event) {
          $(".error").remove();
          delete_row($row, row.forwarder, row.destination, row.expiration);
          event.preventDefault();
          return false;
        });
        $('#forwarder-data-table')
          .find('tbody').append($row)
          .trigger('addRows', [$row, /*resort=*/true]);
      }

      // Initialize table.
      var table = $('#forwarder-data-table');
      $('#forwarder-data-table').tablesorter({
        theme: 'blue',
        sortList: [[0,0], [1,0]],
        widgets: ['zebra', 'formatter', 'filter', 'pager'],
        widgetOptions: {
          formatter_column: {
            '.col-date': function(text, data) {
              data.$cell.attr(data.config.textAttribute, text);
              var expiry = parseInt(text);
              var days = Math.round((expiry - Date.now()/1000)/(24*60*60));
              var display = timestamp_to_date(text);
              if(expiry < Date.now()/1000) {
                data.$cell.addClass('expired');
                var hover = "Expired " + (-days) + " days ago";
              } else if(expiry < Date.now()/1000 + 7 * 24 * 60 * 60 /*1 week*/) {
                data.$cell.addClass('expiring');
                var hover = "Expiring in " + days + " days";
              } else {
                var hover = "Expires on " + display;
              }
              return '<span class="expiry" title="' + hover + '">' + display + '</span>';
            }
          },
          filter_placeholder: {
            search: 'Filter...',
            select: 'Choose...',
            from: 'From...',
            to: 'To...',
          },
          filter_cssFilter: ['', '', '', 'hidden'],
          filter_formatter: {
            // Date (two inputs)
            '.col-date' : function(cell, index) {
              return $.tablesorter.filterFormatter.uiDatepicker( cell, index, {
                textFrom: '',   // "from" label text
                textTo: 'to',       // "to" label text
              });
            }
          },
          filter_saveFilters: true,
          pager_updateArrows: true,
          pager_startPage: 0,
          pager_pageReset: 0,
          pager_size: 5,
          pager_removeRows: false,
          pager_fixedHeight: false,
          pager_savePages: true,
        },
      });
      var response = $.post({
        url: 'fetch.php',
        dataType: 'json'
      }).done((data) => {
        data.forEach((row) => {
          add_forwarder(row);
        });
        $('#forwarder-data-table').trigger('applyWidgets');
      }).fail((data) => {
        $('#forwarder-data').append(`<span class="error">Unknown error</span>`);
        console.error(data);
      });

      function validate_email(matcher) {
        var email = $(matcher).val();
        var domain = email.substring(email.lastIndexOf('@')+1);
        if(domain.toLowerCase() !== "kruskal.net") {
          $(matcher).after(`<span class="error">Unknown domain ${domain}</span>`);
          return;
        }
        return email;
      }

      $('#forwarder-form').submit(function(e) {
        $(".error").remove();
        e.preventDefault();

        // TODO: Add more validation.
        var forwarder = validate_email('#forwarder-email');
        var expiration = $(e.target).prop('checked') ? null : $('#forwarder-expiration').val();

        var response = $.post({
          url: 'add.php',
          data: {
            forwarder: $('#forwarder-email').val(),
            expiration: expiration,
          },
          dataType: 'json'
        }).done((data) => {
          if(data.error) {
            $('#forwarder-errors').append(`<span class="error">${data.error}</span>`);
          } else {
            $('#forwarder-email').val('');
            reset_expiration();
            add_forwarder(data);
          }
        }).fail((data) => {
          $('#forwarder-errors').append(`<span class="error">Unknown error</span>`);
          console.error(data);
        });
      });
    });
  </script>
</body>
</html>