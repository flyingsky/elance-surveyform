<?php if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require 'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

  $name = 'YourNameHere';  // your name
  $email = 'TheEmailYouWantToGetSurveyResult';   // your target email

  // your sender email, should be gmail, if not, please change below $mail.HOST
  $from = 'TheEmailYouUseToSendEmail';           // you can make it the same to target email
  $pwd = 'ThePasswordOfYourEmail';               // your sender email password

  $content = $_POST['content'];

  // echo $content;

  $mail = new PHPMailer;

  // $mail->SMTPDebug = 3;                                 // Enable verbose debug output

  $mail->isSMTP();                                      // Set mailer to use SMTP
  $mail->Host = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
  $mail->SMTPAuth = true;                               // Enable SMTP authentication
  $mail->Username = $from;                              // SMTP username
  $mail->Password = $pwd;                               // SMTP password
  $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
  $mail->Port = 587;                                    // TCP port to connect to

  $mail->From = $from;
  $mail->FromName = 'Survey';
  $mail->addAddress($email, $name);                     // Add a recipient


  $mail->isHTML(true);                                  // Set email format to HTML

  $mail->Subject = 'Survey';
  $mail->msgHTML($content);
  //$mail->AltBody = $content;

  if(!$mail->send()) {
      echo 'Survey could not be sent.';
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  } else {
      echo 'Survey has been sent';
  }
}
else {
?>

<!DOCTYPE html>
<html>
<head lang="en">
  <meta charset="UTF-8">
  <title>Survey</title>

  <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
  <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
  <script src="http://code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
  <script src="jquery.drawPieChart.js"></script>

<!--
  <link rel="stylesheet" href="ui/1.11.2/themes/smoothness/jquery-ui.css">
  <script src="jquery-1.10.2.js"></script>
  <script src="ui/1.11.2/jquery-ui.js"></script>
  <script src="jquery.drawPieChart.js"></script>
-->
  <style>
    *:focus {
      outline: 0;
    }

    body {
      /*font-size: 25px;*/
    }

    .survey { margin: auto auto; position: relative;}
    .item {width: 50%; margin: 20px;}
    .form-item {width: 45%; margin: 20px;}

    .item-title {
      font-size: 25px;
      margin-bottom: 15px;
    }

    .input {
      width: 70%;
      line-height: 40px;
      font-size: 25px;
      border-radius: 10px;
    }

    .input[type=submit] {background-color: #e5e5e5;}

    .pull-left {float: left}
    .pull-right {float: right;}

    #sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
    #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; cursor: move;}
    #sortable li span { position: absolute; margin-left: -1.3em; }
    #sortable .ui-state-default { background: #f5f5f5 !important;}
    .ui-state-highlight { height: 1.5em; line-height: 1.2em; }

    #performanceLabels {margin-bottom: 10px;}
    #performanceLabels span {display: inline-block; width: 20%; margin-left: -4px; cursor: pointer;}
    #performanceLabels .option1 {margin-left: 0px;}
    #performanceLabels .option3 {text-align: center;}
    #performanceLabels .option4 {text-align: right;}
    #performanceLabels .option5 {text-align: right;}

    .slider {background: #ddd; height: 7px;}
    .slider .ui-state-default {background: #9a9a9a;}
    .slider .ui-slider-handle {width: 16px; height: 16px; border-radius: 8px;}

    /** question 3 */
    .question3 {
      position: absolute;
      top: 0px;
      left: 50%;
    }

    #sliders {
      float: left;
    }

    #sliders > span.chart-slider {
      width:200px;
      margin:10px;
      float: left;
      clear: both;
    }

    #sliders label {
      width: 200px;
      margin-left: 10px;
      float: left;
      clear: both;
      padding-left: 5px;
    }

    input[type=checkbox] {
      float: right;
      margin-top: 10px;
    }

    .title-lock {
      text-transform: uppercase;
      float: right;
      margin-right: -10px;
    }

    /** pie chart */
    .chart {
      float: left;
      width: 240px;
      height: 240px;
      top: 50%;
      left: 50%;
    }

    .pieTip {
      position: absolute;
      float: left;
      min-width: 30px;
      max-width: 300px;
      padding: 5px 18px 6px;
      border-radius: 2px;
      background: rgba(255,255,255,.97);
      color: #444;
      font-size: 19px;
      text-shadow: 0 1px 0 #fff;
      text-transform: uppercase;
      text-align: center;
      line-height: 1.3;
      letter-spacing: .06em;
      box-shadow: 0 0 3px rgba(0,0,0,0.2), 0 1px 2px rgba(0,0,0,0.5);
      -webkit-transform: all .3s;
      -moz-transform: all .3s;
      -ms-transform: all .3s;
      -o-transform: all .3s;
      transform: all .3s;
      pointer-events: none;
    }
    .pieTip:after {
      position: absolute;
      left: 50%;
      bottom: -6px;
      content: "";
      height: 0;
      margin: 0 0 0 -6px;
      border-right: 5px solid transparent;
      border-left: 5px solid transparent;
      border-top: 6px solid rgba(255,255,255,.95);
      line-height: 0;
    }
    .chart path { cursor: pointer; }
  </style>

  <script>
    $(function() {
      // sort question
      $( "#sortable" ).sortable({
        placeholder: "ui-state-highlight"
      });
      $( "#sortable" ).disableSelection();

      // slider question
      var questionStep = 10;
      $( "#performanceSlider" ).slider({
        value:20,
        min: 0,
        max: 40,
        step: questionStep,
        slide: function(event, ui) {
          console.log(ui.value);
        }
      });

      var question2Options = $( "#performanceLabels span");
      question2Options.click(function() {
        var index = question2Options.index($(this));
        $("#performanceSlider").slider('value', index * questionStep);
      });

      // chart question
      function getChartSliders() {
        return $('#sliders > span.chart-slider');
      }

      (function question3() {
        var data = $('#performanceLabels span').map(function() {
          return {title: $(this).text()};
        });

        var colors = ["#f7f06c", "#f37648", "#99ce9a", "#8ed3d8", "#cb9ac7"];
        $.each(colors, function(index, color) {
          data[index].color = color;
        });

        // set legend for labels
        var labels = $('#sliders label');
        labels.each(function(index){
          $(this).css('border-left', '18px solid ' + colors[index]);
        });

        var changeTitle = function(index, value) {
          $(labels[index]).text(data[index].title + ': ' + value + '%');
        };

        var drawChart = function(animation) {
          // remove chart
          $('.chart').empty();
          $('.pieTip').remove();

          $(".chart").drawPieChart(data, {
            animation: true
          });
        };

        function getUnlockedSliders () {
          var count = 0;
          getChartSliders().each(function(index, item) {
            if (!$(item).data('locked')) {
              count++;
            }
          });

          return count;
        }

        function getDeltas(changes, count) {
          // keep all delta are int
          var deltas = [];
          var mod = changes % count;
          var delta = (changes - mod) / count;
          var modSign = mod > 0 ? 1 : -1;
          for (var i = count - 1; i >= 0; i--) {
            deltas[i] = delta + (mod !== 0 ? 1 : 0) * modSign
            mod = mod === 0 ? 0 : (mod - modSign);
          }
          return deltas;
        }

        function getMinValueFromUnlockedSlider() {
          var minSlider = null;
          var minValue = 101;
          getChartSliders().each(function(index) {
            var sliderEl = $(this);
            if (!sliderEl.data('locked') && !sliderEl.data('processed') && sliderEl.slider('value') < minValue) {
              minSlider = sliderEl;
              minValue = sliderEl.slider('value');
            }
          });
          return minSlider;
        }

        function getChartSliderSum() {
          var sum = 0;
          getChartSliders().each(function(){
            sum += $(this).slider('value');
          });
          return sum;
        }

        var updateTitleAndChart = function(srcIndex, changes) {
          var countOfOtherUnlockedSliders = getUnlockedSliders() - 1;
          var deltas = getDeltas(changes, countOfOtherUnlockedSliders);

          // update other unlocked sliders
          for (var i = 0; i < countOfOtherUnlockedSliders; i++) {
            var sliderEl = getMinValueFromUnlockedSlider();
            var delta = deltas.shift();
            var oldValue = sliderEl.slider('value');
            var newValue = oldValue  + delta;
            if (newValue < 0) { // avoid 0
              newValue = 0;
              changes -= -oldValue;
              deltas = getDeltas(changes, countOfOtherUnlockedSliders - i - 1);
            } else {
              changes -= delta;
            }

            sliderEl.data('processed', true);

            sliderEl.slider('value', newValue);
            var sliderIndex = sliderEl.data('index');
            changeTitle(sliderIndex, newValue);
            data[sliderIndex].value = newValue;
          }

          // clear handle flag
          getChartSliders().each(function(index) {
            $(this).data('processed', false);
          });

          // check sum <= 100
          var sum = getChartSliderSum();
          if (sum > 100) {
            var srcSliderEl = $(getChartSliders()[srcIndex]);
            var newValue = srcSliderEl.slider('value') - (sum - 100);
            srcSliderEl.slider('value', newValue);
            changeTitle(srcIndex, newValue);
          }

          // redraw
          drawChart(false);
        };

        // setup chart sliders
        getChartSliders().each(function(index) {
          // read initial values from markup and remove that
          var value = parseInt($(this).text(), 10);
          data[index].value = value;

          $(this).empty().slider({
            value: value,
            range: "min",
            animate: true,
            orientation: "horizontal",
            start: function(event, ui) {
              $(this).data('startValue', ui.value);
            },
            slide: function(event, ui) {
              changeTitle(index, ui.value);
            },
            stop: function(event, ui) {
              var startValue = $(this).data('startValue');
              var changes = ui.value - startValue;
              if (changes !== 0) {
                $(this).data('processed', true);
                data[index].value = ui.value;
                updateTitleAndChart(index, -changes);
              }
            }
          });

          $(this).data('index', index);

          changeTitle(index, value);
        });

        // setup lock
        $('#sliders input').click(function(){
          var locked = $(this).is(':checked');
          var index = $('#sliders input').index($(this));
          $(getChartSliders()[index]).data('locked', locked);
        });

        drawChart(false);
      })();

      // setup submit
      function collectSurvey() {
        var result = [];
        // question 1
        var question1 = $('#sortable li').map(function(){
            return $(this).text();
          }).get().join(' / ');
        result.push('1. Rank: ' + question1);

        // question 2
        var selectedLabel = $('#performanceLabels span')[$('#performanceSlider').slider('value') / 10];
        var question2 = $(selectedLabel).text();
        result.push('2. ' + question2);

        // question 3
        var question3 = getChartSliders().map(function(){
          return $(this).slider('value') + '%';
        }).get().join(' - ');
        result.push('3. ' + question3);

        result.push('Name: ' + $('#name').val(), 'Email: ' + $('#email').val());

        return result;
      }

      function validate() {
        // check name and email
        if (!$('#name').val()) {
          alert('name is required');
          return false;
        }

        var email = $('#email').val();
        if (!email) {
          alert('email is required');
          return false;
        }

        if (email.indexOf('@') < 0 || email[email.length - 1] === '@') {
          alert('email is not valid');
          return false;
        }

        return true;
      }

      $('#btnSubmit').click(function(){
        if (!validate()) {
          return false;
        }

        var result = collectSurvey();
        if (!confirm('Are you sure to submit this survey: \n' + result.join('\n'))) {
          return false;
        }

        $('#content').val(result.join(' <br/> '));

        return true;
      });
    });
  </script>

</head>
<body>
<div class="survey">
  <div class="item">
    <div class="item-title">1. Rank in order of importance</div>
    <ul id="sortable">
      <li class="ui-state-default"><span class="ui-icon ui-icon-grip-dotted-vertical"></span>Option 1</li>
      <li class="ui-state-default"><span class="ui-icon ui-icon-grip-dotted-vertical"></span>Option 2</li>
      <li class="ui-state-default"><span class="ui-icon ui-icon-grip-dotted-vertical"></span>Option 3</li>
      <li class="ui-state-default"><span class="ui-icon ui-icon-grip-dotted-vertical"></span>Option 4</li>
    </ul>
  </div>

  <div class="item">
    <div class="item-title">2. Rate my performance</div>
    <div id="performanceLabels">
      <span class="option1">Terrible</span>
      <span class="option2">Not Good</span>
      <span class="option3">Average</span>
      <span class="option4">Good</span>
      <span class="option5">Excellent</span>
    </div>
    <div class="slider" id="performanceSlider"></div>
  </div>

  <div class="item question3">
    <div class="item-title">3. Priorities</div>
    <div class="chart"></div>
    <div id="sliders">
      <span class="title-lock">Lock</span>
      <span class="chart-slider slider">20</span>
      <input type="checkbox">
      <label></label>
      <span class="chart-slider slider">20</span>
      <input type="checkbox">
      <label></label>
      <span class="chart-slider slider">20</span>
      <input type="checkbox">
      <label></label>
      <span class="chart-slider slider">20</span>
      <input type="checkbox">
      <label></label>
      <span class="chart-slider slider">20</span>
      <input type="checkbox">
      <label></label>
    </div>
  </div>

  <form method="post">
    <input type="hidden" name="content" id="content">
    <div class="form-item">
      <input class="input" type="text" name="name" id="name" placeholder="Your name">
    </div>

    <div class="form-item pull-left ">
      <input class="input" type="email" name="email" id="email" placeholder="E-mail address">
    </div>

    <div class="form-item pull-right" style="text-align: center">
      <input class="input" type="submit" value="SUBMIT" id="btnSubmit">
    </div>
  </form>
</div>
</body>
</html>

<?php
}
?>