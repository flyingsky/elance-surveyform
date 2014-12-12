/**
 * Created by ramon on 12/12/14.
 */

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

    $.ajax('postSurvey.php', {
      data: {
        name: $('#name').val(),
        email: $('#email').val(),
        content: result.join(' <br/> ')
      }
    }).success(function(data) {
      alert('Succeed to submit survey, thanks!');
    }).fail(function() {
      alert('Sorry, fail to submit survey, please try it later!');
    });

    return false;
  });
});