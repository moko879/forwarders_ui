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
  <link rel="stylesheet" href="main.css">
</head>
<body>
  <div id="logout">
    <a href="logout.php">Logout</a>
  </div>
  <div id="forwarder-form">
    <form method="post" action="">
      <p>
        <label for="forwarder-email">Forwarder:</label>
        <input type="email" id="forwarder-email" placeholder="Enter Forwarder" name="forwarder">
      </p>
      <p>
        <label for="forwarder-expiration">Expiration Date:</label>
        <input type="date" id="forwarder-expiration" placeholder="Enter Date" name="expiration">
        <label id="forwarder-eternal-label">
          <input type="checkbox" id="forwarder-eternal" placeholder="Never" name="forward-eternal">
          Never
        </label>
      </p>
      <p>
        <button type="submit">Submit</button>
      </p>
    </form>
    <p id="forwarder-errors">
    </p>
  </div>
  <div id="forwarder-data">
    <table id="forwarder-data-table">
      <thead>
        <tr>
          <th>Forwarder</th>
          <th>Destination</th>
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
          <option selected="selected" value="5">5</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
      </form>
    </div>
  </div>
  <script type="application/javascript">
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
          $row.remove();
          $('#forwarder-data-table').trigger('update');
        }).fail((data) => {
          $('#forwarder-errors').append(`<span class="error">Unknown error</span>`);
          console.error(data);
        });
      }

      function add_forwarder(row) {
        var $row = $('<tr><td>'+row.forwarder+'</td>'+
          '<td>'+row.destination+'</td>'+
          '<td>'+row.expiration+'</td>'+
          '<td><a href="#" class="remove">\u2718</a></td></tr>');
        $row.click(function(event) {
          delete_row($row, row.forwarder, row.destination, row.expiration);
        });
        $('#forwarder-data-table')
          .find('tbody').append($row)
          .trigger('addRows', [$row, /*resort=*/true]);
      }

      // Initialize table.
      var table = $('#forwarder-data-table');
      $('#forwarder-data-table').tablesorter({
        theme: 'blue',
        widthFixed: true,
        sortList: [[0,0], [1,0]],
        widgets: ['zebra', 'formatter', 'filter', 'pager'],
        widgetOptions: {
          filter_placeholder: {
            search: 'Filter...',
            select: 'Choose...',
            from: 'From...',
            to: 'To...',
          },
          formatter_column: {
            ".col-date": function(text, data) {
              data.$cell.attr(data.config.textAttribute, text);
              if(parseInt(text) < Date.now()/1000) {
                data.$cell.addClass('expired');
              } else if(parseInt(text) < Date.now()/1000 + 7 * 24 * 60 * 60 /*1 week*/) {
                data.$cell.addClass('expiring');
              }
              return timestamp_to_date(text);
            }
          },
          filter_cssFilter: ['', '', '', 'hidden'],
          filter_formatter : {
            // Date (two inputs)
            ".col-date" : function(cell, index) {
              return $.tablesorter.filterFormatter.uiDatepicker( cell, index, {
                textFrom: '',   // "from" label text
                textTo: 'to',       // "to" label text
              });
            }
          },
          pager_css: {
              container   : 'tablesorter-pager',    // class added to make included pager.css file work
              errorRow    : 'tablesorter-errorRow', // error information row (don't include period at beginning); styled in theme file
              disabled    : 'disabled'              // class added to arrows @ extremes (i.e. prev/first arrows "disabled" on first page)
          },
          // jQuery selectors
          pager_selectors: {
            container   : '.pager',       // target the pager markup (wrapper)
            first       : '.first',       // go to first page arrow
            prev        : '.prev',        // previous page arrow
            next        : '.next',        // next page arrow
            last        : '.last',        // go to last page arrow
            gotoPage    : '.gotoPage',    // go to page selector - select dropdown that sets the current page
            pageDisplay : '.pagedisplay', // location of where the "output" is displayed
            pageSize    : '.pagesize'     // page size selector - select dropdown that sets the "size" option
          },
          pager_updateArrows: true,
          pager_startPage: 0,
          pager_pageReset: 0,
          pager_size: 5,
          pager_removeRows: false,
          pager_fixedHeight: true,
        },
      });
      var response = $.post({
        url: 'fetch.php',
        dataType: 'json'
      }).done((data) => {
        data.forEach((row) => {
          add_forwarder(row);
        });
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
        e.preventDefault();
        $(".error").remove();

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
            $('#forwarder-expiration').val('');
            $('#forwarder-eternal').prop('checked', false);
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