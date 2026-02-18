<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Video Progress</title>

  <script   src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
  <!-- <iframe src="https://iframe.mediadelivery.net/embed/246076/5ac67a4e-37b1-46a1-a779-9d1e0f30c8bc?autoplay=true&loop=false&muted=false&preload=true&responsive=true" loading="lazy" style="border:0;position:absolute;top:0;height:50%;width:50%;" allow="accelerometer;gyroscope;autoplay;encrypted-media;picture-in-picture;" allowfullscreen="true"></iframe> -->

  <p id="watchValue">Watch Value: <span id="value">0</span> seconds</p>

  <script  >
    let getPaused = false;
    let duration = 0;
    let intervalId = null;
    let progressSaved = false;
    let totalVideoDuration = 0;
    let previousPercentage = sessionStorage.getItem('previousPercentage') || 0;

    function pauseAndFetchDuration() {
      const iframe = document.getElementsByTagName('iframe')[0];
      if (iframe) {
        iframe.contentWindow.postMessage({
          context: 'player.js',
          method: 'getCurrentTime'
        }, '*');

         iframe.contentWindow.postMessage({
          context: 'player.js',
          method: 'getDuration'
        }, '*');
      }
    }

    window.addEventListener('message', function(event) {
      let jsonData = event.data;

      if (typeof jsonData === 'string') {
        try {
          jsonData = JSON.parse(jsonData);
        } catch (e) {
          console.error('Error parsing JSON:', e);
          return;
        }
      }

      if (jsonData && jsonData.event) {
        if (jsonData.event === 'getCurrentTime') {
          duration = jsonData.value;
         sessionStorage.setItem('duration', duration);
        }
        if (jsonData.event === 'getDuration') {
              totalVideoDuration = parseInt(jsonData.value);
        }

        if (jsonData.event === 'getPaused') {
          if (jsonData.value === true) {
            if (!progressSaved && duration > 0) {
              const itemId = 4;
              const chapterId = 12434;
              const userId = 1244;
              const webinarId = 12434;

            const watchPercentage = parseInt((duration / totalVideoDuration) * 100);

              console.log(`Saving progress: ${watchPercentage}% watched`);
              saveCourseProgress(itemId, chapterId, webinarId, userId, duration,watchPercentage,totalVideoDuration);
            //   previousPercentage = watchPercentage;
            //   sessionStorage.setItem('previousPercentage', previousPercentage);
              progressSaved = true;
              getPaused = true;
              sessionStorage.setItem('progressSaved', 'true');
              sessionStorage.setItem('duration', duration);

            }

          } else {
            getPaused = false;
            progressSaved = false;
          }
        }
      }
    });

 intervalId = setInterval(function() {
    const iframe = document.getElementsByTagName('iframe')[0];

    if (iframe) {
        // Send the 'getCurrentTime' method request
        iframe.contentWindow.postMessage({
            context: 'player.js',
            method: 'getPaused'
        }, '*');

        pauseAndFetchDuration();
    }
}, 1000);

    // const iframe = document.getElementsByTagName('iframe')[0];
    // iframe.addEventListener('load', function() {
    //   intervalId = setInterval(() => {
    //     iframe.contentWindow.postMessage({
    //       context: 'player.js',
    //       method: 'getPaused'
    //     }, '*');

    //     pauseAndFetchDuration();
    //   }, 1000);
    // });

    window.addEventListener('beforeunload', () => {
      if (intervalId) {
        clearInterval(intervalId);
      }
    });

    function saveCourseProgress(itemId, chapterId, webinarId, userId, watchedDuration,watchPercentage,totalVideoDuration) {
      $.ajax({
        url: "{{ route('store.watched.duration') }}", // Laravel route
        method: 'POST',
        data: {
          _token: '{{ csrf_token() }}', // CSRF token for security
          item_id: itemId,
          user_id: userId,
          webinar_id: webinarId,
          chapter_id: chapterId,
          watched_duration: watchedDuration,
          watch_percentage: watchPercentage,
          total_duration: totalVideoDuration
        },
        success: function(response) {
          console.log('Course progress saved successfully!');

        },
        error: function(xhr) {
          console.error('Error saving progress:', xhr.responseText);
        }
      });
    }
    window.onload = function() {
      const savedDuration = sessionStorage.getItem('currentDuration');
       const sessionDuration = sessionStorage.getItem('duration');
      if (savedDuration && sessionDuration) {
        duration = parseInt(sessionDuration);
      }
    //   const savedPercentage = sessionStorage.getItem('previousPercentage');
    //   if (savedPercentage) {
    //     previousPercentage = parseInt(savedPercentage);
    //   }
    };
  </script>
</body>
</html>
