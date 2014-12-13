/**
 * Created by ramon on 12/12/14.
 */

$(function() {
  var mgr = {};

  // sort question
  mgr.initSort = function ($question) {
    $question.children(".sortable").sortable({
      placeholder: "ui-state-highlight"
    });
    $question.children(".sortable").disableSelection();
  };

  mgr.collectSurveySort = function($question) {
    var result = $question.find('.sortable li').map(function(){
      return $(this).text();
    }).get().join(' / ');

    return $question.find('.question-title').text() + ': ' + result;
  };

  // slider question
  mgr.initSlider = function ($question, onlyAdjustLabels) {
    var question2Options = $question.find(".slider-labels span");
    var sliderMax = question2Options.length - 1;
    var defaultValue = parseInt(sliderMax / 2);
    var $slider = $question.children('.slider');

    if (!onlyAdjustLabels) {
      // setup slider
      $slider.slider({
        value: defaultValue,
        min: 0,
        max: sliderMax,
        step: 1
      });

      // add click handler on label
      question2Options.click(function() {
        var index = question2Options.index($(this));
        $slider.slider('value', index);
      });
    }

    // adjust label position
    var sliderWidth = $slider.width();
    var delta = sliderWidth / (question2Options.length - 1);
    question2Options.each(function(index){
      var $label = $(this);
      var labelWidth = $label.width();
      var left = Math.min(Math.max(delta * index - labelWidth / 2, 0), sliderWidth - labelWidth);
      $label.css('left', left + 'px');
    });
  };

  mgr.collectSurveySlider = function($question) {
    var selectedLabel = $question.find('.slider-labels span')[$question.find('.slider').slider('value')];
    var result = $(selectedLabel).text();
    return $question.find('.question-title').text() + ': ' + result;
  };

  // chart question
  function getChartSliders($question) {
    return $question.find('.sliders > span.chart-slider');
  }

  mgr.initChart = function($question) {
    var data = [];
    var questionId = 'chart-' + new Date().getTime();

    getChartSliders($question).each(function(){
      data.push({title: $(this).attr('data-title')});
    });

    var colors = ["#f7f06c", "#f37648", "#99ce9a", "#8ed3d8", "#cb9ac7"];
    $.each(colors, function(index, color) {
      data[index].color = color;
    });

    // set legend for labels
    var labels = $question.find('.sliders label');
    labels.each(function(index){
      $(this).css('border-left', '18px solid ' + colors[index]);
    });

    var changeTitle = function(index, value) {
      $(labels[index]).text(data[index].title + ': ' + value + '%');
    };

    var drawChart = function(animation) {
      // remove chart
      $question.find('.chart').empty();
      $('.' + questionId).remove();

      $question.find(".chart").drawPieChart(data, {
        animation: true,
        tipClass: 'pieTip ' + questionId
      });
    };

    function getUnlockedSliders () {
      var count = 0;
      getChartSliders($question).each(function(index, item) {
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
        deltas[i] = delta + (mod !== 0 ? 1 : 0) * modSign;
        mod = mod === 0 ? 0 : (mod - modSign);
      }
      return deltas;
    }

    function getMinValueFromUnlockedSlider() {
      var minSlider = null;
      var minValue = 101;
      getChartSliders($question).each(function(index) {
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
      getChartSliders($question).each(function(){
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
      getChartSliders($question).each(function(index) {
        $(this).data('processed', false);
      });

      // check sum <= 100
      var sum = getChartSliderSum();
      if (sum > 100) {
        var srcSliderEl = $(getChartSliders($question)[srcIndex]);
        var newValue = srcSliderEl.slider('value') - (sum - 100);
        srcSliderEl.slider('value', newValue);
        changeTitle(srcIndex, newValue);
      }

      // redraw
      drawChart(false);
    };

    // setup chart sliders
    getChartSliders($question).each(function(index) {
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
    $('.sliders input').click(function(){
      var locked = $(this).is(':checked');
      var index = $('.sliders input').index($(this));
      $(getChartSliders($question)[index]).data('locked', locked);
    });

    drawChart(false);
  };

  mgr.collectSurveyChart = function($question) {
    var result = getChartSliders($question).map(function(){
      return $(this).slider('value') + '%';
    }).get().join(' - ');

    return $question.find('.question-title').text() + ': ' + result;
  };

  mgr.collectSurveyInput = function($question) {
    var result = $question.val();
    return $question.attr('data-title') + ': ' + result;
  };

  function execute(methodType) {
    var result = [];
    $('.question').each(function(){
      var $question = $(this);
      var questionTypes = ['question-sort', 'question-slider', 'question-chart', 'question-input'];
      $(questionTypes).each(function(index, type) {
        if ($question.hasClass(type)) {
          var m = type.replace('question-', '');
          var method = mgr[methodType + m[0].toUpperCase() + m.substr(1)];
          if (method) {
            result.push(method($question));
          }
        }
      });
    });

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

    var result = execute('collectSurvey');
    if (!confirm('Are you sure to submit this survey: \n' + result.join('\n'))) {
      return false;
    }

    $('#btnSubmit').val('SENDING.....');

    $.ajax('survey.php', {
      type: 'POST',
      data: {
        name: $('#name').val(),
        email: $('#email').val(),
        content: result.join(' <br/> ')
      }
    }).success(function(data) {
      if (data === 'ok') {
        alert('Succeed to submit survey, thanks!');
      } else {
        alert(data);
      }
    }).fail(function() {
      alert('Sorry, fail to submit survey, please try it later!');
    }).complete(function() {
      $('#btnSubmit').val('SUBMIT');
    });

    return false;
  });

  // change slider labels if window size is changed
  $(window).resize(function() {
    $('.question-slider').each(function(){
      mgr.initSlider($(this), true);
    });
  });

  execute('init');
});