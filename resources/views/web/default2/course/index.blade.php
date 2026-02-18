@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/css/css-stars.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video-js.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/css/app-ast.css">
           <link rel="stylesheet" href="{{ config('app.js_css_url') }}/asttroloknew/assets/vendors/wrunner-html-range-slider-with-2-handles/css/wrunner-default-theme.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/asttroloknew/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/asttroloknew/assets/design_1/css/parts/swiperjs.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/asttroloknew/assets/design_1/css/parts/products_lists.min.css">

<meta name=”robots” content=”noindex”>

    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/video/video-js.min.css">

    <link
      rel="stylesheet"
      href="https://unpkg.com/animate.css@4.1.1/animate.css"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=STIX+Two+Text:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />

<style>
  body{
     font-family: "Inter", sans-serif  !important;
  }
*[style] {
  font-family: "Inter", sans-serif  !important;
}
.roadmap-table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #E5E7EB;
    background: #FFFFFF;
    font-size: 16px;                         /* thoda bada text */
    color: #111827;
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.06);
}

.frame427322615-text189 {
    top: 23px !important;
}

.frame427322615-text183 {
    top: 8px !important;
}
/* 2px chhota size */
.frame427322615usp .frame427322615-certification {
    padding: 10px 14px !important;
    transform: scale(0.88);   /* 0.90 se 0.88 */
    transform-origin: top left;
    align-items: center;
}

/* Text ka size 2px chhota */
.frame427322615usp .frame427322615-text113 {
    font-size: 16px !important;  /* 18px se 16px */
    line-height: 1.5 !important;
    font-weight: 500;
}

/* Icon ka size 2px chhota */
.frame427322615usp .frame427322615-vector10 {
    width: 42px !important;   /* 44px se 42px */
    height: 42px !important;
}

/* Agar 2 lines hai to */
.frame427322615usp .frame427322615-text113 span {
    display: block;
}

.frame427322615usp .frame427322615-text113 span:first-child {
    font-size: 16px !important;  /* 18px se 16px */
    font-weight: 600;
}

.frame427322615usp .frame427322615-text113 span:last-child {
    font-size: 15px !important;  /* 17px se 15px */
    font-weight: 400;
}


.roadmap-table tbody td {
    padding: 24px 24px;                      /* row height ~ Figma jaisi */
    border-bottom: 1px solid #E5E7EB;
    vertical-align: top;
    font-weight: 400;
    color: #374151;
}

.roadmap-table tbody tr:last-child td {
    border-bottom: none;
}

.roadmap-table tbody td.month {
    white-space: nowrap;
    font-weight: 600;
}


.roadmap-table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 16px;                 /* rounded corners */
    overflow: hidden;                    /* radius apply hone ke लिए */
    border: 1px solid #E5E7EB;
    background: #FFFFFF;
    font-size: 15px;
    color: #111827;

    /* halka shadow – card feel */
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.06);
}
.roadmap-table tbody td {
    padding: 22px 20px;                  /* pehle 18px tha, ab 22px */
    border-bottom: 1px solid #E5E7EB;
    vertical-align: top;
    font-weight: 400;
    color: #374151;
}
.roadmap-table tbody td {
    padding: 28px 20px;
}

.roadmap-table-wrapper {
    margin-top: 16px;
    margin-bottom: 24px;
}
.roadmap-table thead th {
    text-align: left;
    padding: 16px 24px;                      /* header height */
    font-weight: 600;
    color: #6B7280;
    background: #F9FAFB;
    border-bottom: 1px solid #E5E7EB;
}

.roadmap-table tbody tr {
    background: #FFFFFF;         /* rows pure white */
}

.roadmap-table tbody td {
    padding: 18px 20px;
    border-bottom: 1px solid #E5E7EB;  /* row separator same grey */
    vertical-align: top;
    font-weight: 400;
    color: #374151;                     /* gray-700 body text */
}

.roadmap-table tbody tr:last-child td {
    border-bottom: none;
}

.roadmap-table tbody td.month {
    white-space: nowrap;
    font-weight: 600;                    /* bold month */
}

.frame427322615-frame427322611 {
    height: auto !important;
    min-height: auto !important;
    max-height: none !important;
}
.frame427322615-button61 {
    top: 110px;
    left: 17px;
    width: 385px;
    height: 42px;
    display: flex
;
    overflow: hidden;
    position: absolute;
    box-shadow: 0px 4px 6px -1px rgba(0, 0, 0, 0.10000000149011612);
    align-items: flex-start;
    flex-shrink: 0;
    border-color: rgba(50, 160, 40, 1);
    border-style: solid;
    border-width: 1px;
    border-radius: 12px;
    background-color: rgba(50, 160, 40, 1);
}
.frame427322615-button62 {
    top: 162px;
    left: 17px;
    width: 385px;
    height: 42px;
    display: flex
;
    overflow: hidden;
    position: absolute;
    box-shadow: 0px 4px 6px -1px rgba(0, 0, 0, 0.10000000149011612);
    align-items: flex-start;
    flex-shrink: 0;
    border-color: rgba(245, 229, 50, 1);
    border-style: solid;
    border-width: 1px;
    border-radius: 12px;
    /* background-color: #f7f8f9ff !important; */
}
.frame427322615-button63 {
    top: 210px;
    left: 17px;
    width: 385px;
    height: 42px;
    display: flex
;
    overflow: hidden;
    position: absolute;
    box-shadow: 0px 4px 6px -1px rgba(0, 0, 0, 0.10000000149011612);
    align-items: flex-start;
    flex-shrink: 0;
    border-color: rgba(160, 52, 40, 1);
    border-style: solid;
    border-width: 1px;
    border-radius: 12px;
    /* background-color: rgba(50, 160, 40, 1); */
}
.frame427322615-background-shadow {
    width: 454px;
    height: 489px !important;
}
.frame427322615-text1834 {
    top: 11px;
    left: 74.5px;
    /* color: rgba(255, 255, 255, 1); */
    width: 236px;
    height: auto;
    position: absolute;
    font-size: 16px;
    font-style: Medium;
    text-align: center;
    font-family: Inter;
    font-weight: 500;
    line-height: 24px;
    font-stretch: normal;
    text-decoration: none;
}
.frame427322615-background-border2 {
    height: 450px !important;

}
.frame427322615-text186 {
    border: none;
    background: none;
    width: 100px;
    padding: 0;
}
  </style>

@endpush
{{ session()->put('my_test_key',url()->current())}}

@section('content')

   <div class="cover-content pt-40">
      <link href="{{ config('app.js_css_url') }}/asttroloknew/index.css" rel="stylesheet" />
 {{--@foreach($course as $courses) --}}
      <div class="container">
        <div class="frame427322615-frame427322615">
          <div class="frame427322615-frame427322614">
            <div class="frame427322615-header1">
              <div class="frame427322615-header2">
                <div class="frame427322615-text100">
                  <div class="frame427322615-head">
                    <span class="frame427322615-text101">
                      {{$course->extraDetails->subtitle	}}
                    </span>
                  </div>
                  <span class="frame427322615-text102">
                    <h1 class="frame427322615-text103">
                      {{$course->extraDetails->heading_main	}}
                    </h1>
                    <span style="color: green;">{{$course->extraDetails->heading_sub	}}</span>
                    <br />
                  <span>{{$course->extraDetails->heading_extra	}}</span>
                  </span>
                  <span class="frame427322615-text107">
                   {{$course->extraDetails->subdescription}}
                  </span>
                  <span class="frame427322615-text108">
                    <span class="frame427322615-text109">
                     {{ $course->extraDetails->additional_description	 }}
                    </span>
                    <br />
                    <span>{{ $course->extraDetails->extra_description }}</span>
                  </span>

                    <form action="/cart/store" method="post" style="flex:1 1 0;">
                      @csrf
                      <input type="hidden" name="item_id" value="{{ $course->id }}">
                      <input type="hidden" name="item_name" value="webinar_id">
                      
                       @if($hasBought or !empty($course->getInstallmentOrder()))
    {{-- User ne already purchase kar liya hai --}}
    <a href="{{ $course->getLearningPageUrl1() }}" 
       class="frame427322615-button1 btn-primary" 
       style="flex:1 1 0; text-decoration: none; display: flex; align-items: center; justify-content: center;">
        <span class="frame427322615-text112">
            Start Learning
        </span>
    </a>
@elseif($course->price > 0)
    {{-- User ne abhi purchase nahi kiya --}}
    <form action="/cart/store" method="post" style="flex:1 1 0;">
        @csrf
        <input type="hidden" name="item_id" value="{{ $course->id }}">
        <input type="hidden" name="item_name" value="webinar_id">
        
        <button type="button" class="frame427322615-button1 btn-success buy_now js-course-direct-payment">
            <span class="frame427322615-text112">
                {{ $course->extraDetails->cta_text }}&nbsp;
            </span>
        </button>
    </form>
@else
    {{-- Free course ya koi aur condition --}}
    <a href="{{ $course->getLearningPageUrl1() }}" 
       class="frame427322615-button1 btn-primary">
        <span class="frame427322615-text112">
           Start Learning
        </span>
    </a>
@endif
                  </form>
                </div>
                <div class="frame427322615-frame427322616">
                    
                    @php
$imagePath = $course->getImage();

$imagePath = str_replace('webp/', '', $imagePath);
@endphp
                  <img
                    src="{{ config('app.img_dynamic_url') }}{{ $imagePath }}"
                    alt="{{$course->title}}"
                    class="frame427322615-astrology-learning-program1"
                  />
                </div>
              </div>
            </div>
            <div class="frame427322615usp">
                  @php
    $materialTexts = json_decode($course->extraDetails->material_text ?? '[]', true);
    $materialIcons = json_decode($course->extraDetails->material_icon ?? '[]', true);
@endphp

@foreach(array_map(null, $materialTexts, $materialIcons) as [$text, $icon])
    <div class="frame427322615-certification">
        <span class="frame427322615-text113">

            @if(is_array($text))
                <span>{{ $text[0] }}</span><br>
                <span>{{ $text[1] }}</span>
            @else
                {{ $text }}
            @endif

        </span>

        <img
            src="{{ asset($icon) }}"
            alt="Material Icon"
            class="frame427322615-vector10"
        />
    </div>
@endforeach
            </div>
          </div>

          <div class="frame427322615-frame427322613">

            <div class="frame427322615-frame427322612">
              {{--<div class="frame427322615-border1">
                <div class="frame427322615-background10">
                  <img
                    src="/public/public/svg1615-3n7t.svg"
                    alt="SVG1615"
                    class="frame427322615svg10"
                  />
                  <span class="frame427322615-text120">
                    <span>{{ $course->extraDetails->plan_duration_option}}</span>
                    <br />
                    <span>{{ $course->extraDetails->plan_movie}}</span>
                  </span>
                  <span class="frame427322615-text124">₹{{ $course->extraDetails->price_suffix}}+ </span>
                  <img
                    src="{{ asset($course->extraDetails->plan_icon) }}"
                    alt="Vector1615"
                    class="frame427322615-vector14"
                  />
                </div>
                <div class="frame427322615-background-border-shadow">
                  <img
                    src="/public/public/svg1616-uv3f.svg"
                    alt="SVG1616"
                    class="frame427322615svg11"
                  />
                  <span class="frame427322615-text125">{{ $course->extraDetails->plan_duration}}</span>
                  <span class="frame427322615-text126">{{ $course->extraDetails->plan_cancel_text}}</span>
                  <span class="frame427322615-text127">{{ $course->extraDetails->plan_type}}</span>
                  <span class="frame427322615-text128">₹{{ $course->extraDetails->plan_price}}</span>
                  <div class="frame427322615-background11">
                    <span class="frame427322615-text129">Lifetime Saving</span>
                    <img
                      src="/public/public/border1616-0awh-200h.png"
                      alt="Border1616"
                      class="frame427322615-border2"
                    />
                    <img
                      src="/public/public/border1616-pz1i-200h.png"
                      alt="Border1616"
                      class="frame427322615-border3"
                    />
                  </div>
                </div>
                <div class="frame427322615-background-border1">
                  <span class="frame427322615-text130">VS</span>
                </div>
                <span class="frame427322615-text131">
                  {{ $course->extraDetails->comparison_text}}
                </span>
              </div>--}}

 <div class="custom-tabs mt-16 ">
     <div class="frame427322615-frame427322536 mb-10">
                    <div class="frame427322615-frame427322535">
                      <div class="frame427322615-frame427322535">
    <span class="frame427322615-text194" style="white-space: nowrap; width: auto; display: inline-block;">
        About This Course
    </span>
   
</div>
                    
                    </div>
                    <img
                      src="/public/public/horizontaldivider2863-fyl2-200h.png"
                      alt="HorizontalDivider2863"
                      class="frame427322615-horizontal-divider3 "
                    />
                  </div>
    <div class="course-tabs-card position-relative">
        <div class="course-tabs-card__mask"></div>

        <div class="position-relative d-flex align-items-center gap-20 gap-lg-40 flex-wrap  px-16 px-lg-20 rounded-12 z-index-2 w-100"style="margin-left: -20px;">
          <style>
    /* Active tab ki underline ko niche shift karne ke liye */
    .navbar-item.active {
        border-bottom: 1px solid rgba(50,160,40,1); /* green line */
        padding-bottom: 8px; /* jitna niche chahiye utna badha sakte ho */
    }
</style>

<div class="navbar-item d-flex-center cursor-pointer active" data-tab-toggle data-tab-href="#aboutCourseTab">
   
    <img src="/public/public/svg1628-kat.svg" alt="SVG1628" class="mr-10">
    <span class="frame427322615-text133z" style="
        color: rgba(50, 160, 40, 1);
        font-size: 14px;
        text-align: center;
        font-family: Inter;
        font-weight: 700;
        line-height: 20px;
        font-stretch: normal;
        text-decoration: none;
    ">About the Course</span>
</div>

            {{--<div class="navbar-item d-flex-center cursor-pointer " data-tab-toggle data-tab-href="#contentTab">
             <img src="/public/public/svg1628-fn5.svg" alt="SVG1628" class="mr-10">   <span class="frame427322615-text134z" style="
    color: rgba(50, 160, 40, 1);
    font-size: 14px;
    text-align: center;
    font-family: Inter;
    font-weight: 700;
    line-height: 20px;
    font-stretch: normal;
    text-decoration: none;
">WHY CHOOSE THIS</span>
            </div>--}}

        </div>
    </div>

    <div class="custom-tabs-body mt-16">

        <div class="custom-tabs-content active" id="aboutCourseTab">
          <div class="mt-20">

        <div class="mt-15 course-description">
            <span class="frame427322615-text135">
                  <span class="frame427322615-text136">
                   {!! $course->description !!}
                  </span>

                </span>
        </div>
    </div>

        </div>

        <div class="custom-tabs-content " id="contentTab">
              @foreach($whyChooseUs as $item)
        <div class="frame427322615-item1">
                  <img
                    src="/public/public/svg1629-5w1e.svg"
                    alt="SVG1629"
                    class="frame427322615svg14"
                  />
                  <span class="frame427322615-text138">
                    <span class="frame427322615-text139">{{$item->title}}:</span>
                    <span>{{$item->answer}}</span>
                  </span>
                </div>
                
              @endforeach   
        </div>
    </div>

</div>
             @include('web.default2.course.newtab.freecontent')

                    <div class="frame427322615-frame427322544">
            <div class="frame427322615-frame427322539">
              <div class="frame427322615-frame427322538">
                <div class="frame427322615-frame427322537">
                  <div class="frame427322615-frame427322536">
                    <div class="frame427322615-frame427322535">
                      <span class="frame427322615-text194">
                        What you will get
                      </span>
                    
                    </div>
                    <img
                      src="/public/public/horizontaldivider2863-fyl2-200h.png"
                      alt="HorizontalDivider2863"
                      class="frame427322615-horizontal-divider3"
                    />
                  </div>
                   @php
                    $learnText = $course->extraDetails->learn_title ?? '';
                    $learnDescription = $course->extraDetails->learn_description ?? '';
                    @endphp

                  <span class="frame427322615-text195">
                    {{ $learnText}}
                  </span>
                </div>
                <span class="frame427322615-text196">
                  <span class="frame427322615-text197">
                    {{ $learnDescription }}
                  </span>
                  <!--<span>You’ll receive:</span>-->
                </span>
              </div>
              <div class="frame427322615-group64">
 @php
$learnTexts = $course->extraDetails ? json_decode($course->extraDetails->learn_text, true) ?? [] : [];
$learnIcons = $course->extraDetails ? json_decode($course->extraDetails->learn_icon, true) ?? [] : [];
@endphp

@foreach(array_map(null, $learnTexts, $learnIcons) as [$text, $icon])
    <div class="frame427322615-certification">
        <img src="{{ asset($icon) }}" alt="Learn Icon" class="frame427322615-background-border3"/>
        <span class="frame427322615-text199">{!! nl2br(e($text)) !!}</span>
    </div>
@endforeach
              </div>
            </div>
        @include('web.default2.course.newtab.contet')
          </div>
         @include('web.default2.course.newtab.bonus')
  @if($course->teacher->id == 1015) 
          <div class="frame427322615-frame427322568">
            <div class="frame427322615-frame427322567">
              <div class="frame427322615-frame427322559">
                <span class="frame427322615-text251">
                  <span class="frame427322615-text252">Meet Your Mentor:</span>
                  <span>Alok Khandelwal</span>
                </span>
                <img
                  src="/public/public/horizontaldivider2873-3bip-200h.png"
                  alt="HorizontalDivider2873"
                  class="frame427322615-horizontal-divider4"
                />
                <span class="frame427322615-text254">
                  <span class="frame427322615-text255">
                    Founder of Asttrolok and a renowned Vedic Astrologer, Alok
                    Khandelwal has taught 50,000+ students across 70+ countries.
                    With degrees in Psychology and Economics and two decades of
                    teaching experience, he transforms ancient wisdom into
                    modern, applicable science
                  </span>
                  <br />
                  <br />
                  <span>
                    Astrology is not prediction it’s self-discovery through data
                    and consciousness.
                  </span>
                  <br />
                  <span>- Alok Khandelwal</span>
                  <br />
                  <br />
                </span>
              </div>
              <div class="frame427322615-frame427322566">
                <div class="frame427322615-frame427322565">
                  <div class="frame427322615-frame427322561">
                    <div class="frame427322615-background17">
                      <img
                        src="/public/public/svg2873-376o.svg"
                        alt="SVG2873"
                        class="frame427322615svg25"
                      />
                    </div>
                    <div class="frame427322615-frame427322560">
                      <span class="frame427322615-text263">
                        MBA (Marketing)
                      </span>
                      <span class="frame427322615-text264">MA (Economics)</span>
                    </div>
                  </div>
                  <div class="frame427322615-frame427322564">
                    <div class="frame427322615-background18">
                      <img
                        src="/public/public/svg2873-8823.svg"
                        alt="SVG2873"
                        class="frame427322615svg26"
                      />
                    </div>
                    <span class="frame427322615-text265">
                      Founder : Asttrolok
                    </span>
                  </div>
                </div>
                <div class="frame427322615-frame427322563">
                  <img
                    src="/public/public/vector2873-r6nf.svg"
                    alt="Vector2873"
                    class="frame427322615-vector25"
                  />
                  <div class="frame427322615-frame427322562">
                    <span class="frame427322615-text266">Jyotish Bhushan</span>
                    <span class="frame427322615-text267">Jyotish Ratna</span>
                    <span class="frame427322615-text268">Jyotish Rishi</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="frame427322615-group28">
              <img
                src="/public/public/ellipse102873-dm1-300h.png"
                alt="Ellipse102873"
                class="frame427322615-ellipse10"
              />
              <img
                src="/public/public/ellipse112873-jw8-300h.png"
                alt="Ellipse112873"
                class="frame427322615-ellipse11"
              />
              <img
                src="/public/public/bgremove9ad4f42e00bgremoved176035772783712873-1988-300w.png"
                alt="bgremove9ad4f42e00bgremoved176035772783712873"
                class="frame427322615-bgremove9ad4f42e00bgremoved17603577278371"
              />
            </div>
          </div>
  @endif
          <div class="frame427322615-border5">
            <div class="frame427322615-frame427322576">
              <div class="frame427322615-frame427322577">
                <div class="frame427322615-frame427322572">
                  <div class="frame427322615-frame427322571">
                    <div class="frame427322615-frame427322570">
                      <img
                        src="/public/public/svg2876-lynq.svg"
                        alt="SVG2876"
                        class="frame427322615svg27"
                      />
                      <span class="frame427322615-text269">Risk Meter</span>
                    </div>
                    <div class="frame427322615-background19">
                      <img
                        src="/public/public/background2876-1i0h-200h.png"
                        alt="Background2876"
                        class="frame427322615-background20"
                      />
                    </div>
                  </div>
                  <span class="frame427322615-text270">~ Zero</span>
                </div>
                <div class="frame427322615-frame427322574">
                  <div class="frame427322615-frame427322573">
                    <img
                      src="/public/public/svg2876-ecf.svg"
                      alt="SVG2876"
                      class="frame427322615svg28"
                    />
                    <span class="frame427322615-text271">Risk Level: Zero</span>
                  </div>
                  <span class="frame427322615-text272">Cancel anytime</span>
                </div>
              </div>
              <div class="frame427322615-frame427322470">
                <div class="frame427322615-frame427322575">
                  <span class="frame427322615-text273">
                                       {{$course->extradetails->risk_title}}

                  </span>
                  <span class="frame427322615-text274">
                                     {{$course->extradetails->risk_description}}

                  </span>
                </div>
              </div>
            </div>
          </div>

@php
    // Raw values
    $monthsRaw   = $course->extraDetails->certification_time ?? '[]';
    $focusRaw    = $course->extraDetails->certification_focus ?? '[]';
    $outcomeRaw  = $course->extraDetails->certification_outcome ?? '[]';

    // Function to remove extra quotes and brackets
    $clean = function ($value) {
        return trim($value, "[]\"'");
    };

    // Function to decode or fallback to comma explode
    $parse = function ($raw) use ($clean) {
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            $data = array_filter(array_map('trim', explode(',', $raw)));
        }

        // Clean each value
        return array_values(array_map($clean, $data));
    };

    // Parse all fields
    $months  = $parse($monthsRaw);
    $focus   = $parse($focusRaw);
    $outcome = $parse($outcomeRaw);

    // Determine table row count
    $rows = max(count($months), count($focus), count($outcome));
@endphp


<div class="frame427322615-frame427322580">
    <div class="frame427322615-frame427322578">
        <span class="frame427322615-text275">
            {{ $course->extraDetails->certificate_title }}
        </span>

        <table class="roadmap-table">
            <thead>
                <tr>
                    <th>
                        {{ isset($course) && isset($course->id) && $course->id == 2112 
                            ? 'Day' 
                            : 'Month' 
                        }}
                    </th>

                    <th>Focus</th>
                    <th>Outcome</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < $rows; $i++)
                    <tr>
                        <td class="month">
                            {{ $months[$i] ?? '' }}
                        </td>
                        <td>
                            {{ $focus[$i] ?? '' }}
                        </td>
                        <td>
                            {{ $outcome[$i] ?? '' }}
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
            <div class="frame427322615-frame427322579">
              <!-- <div class="frame427322615-link25">
                 <a href="/course/direct-payment" style="text-decoration: none;">
                <span class="frame427322615-text297">
                  Start Your First Class Free
                </span>
                
              </a>
              </div> -->
              <form action="/cart/store" method="post" style="flex:1 1 0;">
                      @csrf
                      <input type="hidden" name="item_id" value="{{ $course->id }}">
                      <input type="hidden" name="item_name" value="webinar_id">
                      @if($hasBought or !empty($course->getInstallmentOrder()))
    
        <button type="submit" class="frame427322615-button1 btn-primary">
            <span class="frame427322615-text297">
                Start Learning
            </span>
        </button>
@else
                      <button type="button" class="frame427322615-button1 btn-success buy_now js-course-direct-payment">
                          <span class="frame427322615-text297">
                             {{ $course->extraDetails->cta_text }} 
                          </span>
                      </button>
@endif
                  </form>
              <div class="frame427322615-group56">
                <div class="frame427322615-background21">
                  <div class="frame427322615-paragraph-background">
                    <img
                      src="/public/public/image32878-z0u9-200h.png"
                      alt="image32878"
                      class="frame427322615-image3"
                    />
                  </div>
                </div>
                <div class="frame427322615-background22">
                  <div class="frame427322615-overlay">
                    <span class="frame427322615-text298">
                      Carrer in 6 Months
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
             @include('web.default2.course.newtab.faq')
          <div class="frame427322615-frame427322595">
            <div class="frame427322615-frame427322594">
              <span class="frame427322615-text306">Reviews</span>
              <img
                src="/public/public/horizontaldivider2881-zbc4-200h.png"
                alt="HorizontalDivider2881"
                class="frame427322615-horizontal-divider6"
              />
            </div>
            <div class="frame427322615-container19">
              <div class="frame427322615-frame427322600">
                <div class="frame427322615-frame427322500">
                  <span class="frame427322615-text307">
                    by 7,438 learners for course quality, clarity &amp; mentor
                    support
                  </span>
                  <span class="frame427322615-text308">4.85</span>
                  <img
                    src="/public/public/vector2882-7x6.svg"
                    alt="Vector2882"
                    class="frame427322615-vector26"
                  />
                  <img
                    src="/public/public/vector2882-py7.svg"
                    alt="Vector2882"
                    class="frame427322615-vector27"
                  />
                  <img
                    src="/public/public/vector2882-69t.svg"
                    alt="Vector2882"
                    class="frame427322615-vector28"
                  />
                  <img
                    src="/public/public/vector2882-hk1.svg"
                    alt="Vector2882"
                    class="frame427322615-vector29"
                  />
                  <img
                    src="/public/public/vector2882-f5i8.svg"
                    alt="Vector2882"
                    class="frame427322615-vector30"
                  />
                  <img
                    src="/public/public/vector2882-5tq.svg"
                    alt="Vector2882"
                    class="frame427322615-vector31"
                  />
                  <img
                    src="/public/public/vector2882-a17.svg"
                    alt="Vector2882"
                    class="frame427322615-vector32"
                  />
                  <img
                    src="/public/public/vector2882-46bk.svg"
                    alt="Vector2882"
                    class="frame427322615-vector33"
                  />
                  <img
                    src="/public/public/vector2882-t8c4.svg"
                    alt="Vector2882"
                    class="frame427322615-vector34"
                  />
                  <img
                    src="/public/public/vector2882-h03t.svg"
                    alt="Vector2882"
                    class="frame427322615-vector35"
                  />
                  <img
                    src="/public/public/vector2882-afbf.svg"
                    alt="Vector2882"
                    class="frame427322615-vector36"
                  />
                  <img
                    src="/public/public/vector2882-6nyh.svg"
                    alt="Vector2882"
                    class="frame427322615-vector37"
                  />
                  <img
                    src="/public/public/vector2882-hle9.svg"
                    alt="Vector2882"
                    class="frame427322615-vector38"
                  />
                  <img
                    src="/public/public/vector2882-433k.svg"
                    alt="Vector2882"
                    class="frame427322615-vector39"
                  />
                  <img
                    src="/public/public/vector2882-flh.svg"
                    alt="Vector2882"
                    class="frame427322615-vector40"
                  />
                  <img
                    src="/public/public/vector2882-oey.svg"
                    alt="Vector2882"
                    class="frame427322615-vector41"
                  />
                  <img
                    src="/public/public/vector2882-rvmb.svg"
                    alt="Vector2882"
                    class="frame427322615-vector42"
                  />
                  <img
                    src="/public/public/vector2882-tb69.svg"
                    alt="Vector2882"
                    class="frame427322615-vector43"
                  />
                  <img
                    src="/public/public/vector2882-dp0j.svg"
                    alt="Vector2882"
                    class="frame427322615-vector44"
                  />
                  <img
                    src="/public/public/vector2882-tpjh.svg"
                    alt="Vector2882"
                    class="frame427322615-vector45"
                  />
                  <img
                    src="/public/public/vector2882-9yf.svg"
                    alt="Vector2882"
                    class="frame427322615-vector46"
                  />
                  <img
                    src="/public/public/vector2882-hm8p.svg"
                    alt="Vector2882"
                    class="frame427322615-vector47"
                  />
                  <img
                    src="/public/public/vector2882-szq.svg"
                    alt="Vector2882"
                    class="frame427322615-vector48"
                  />
                  <img
                    src="/public/public/vector2882-nqkf.svg"
                    alt="Vector2882"
                    class="frame427322615-vector49"
                  />
                  <img
                    src="/public/public/vector2882-mzd.svg"
                    alt="Vector2882"
                    class="frame427322615-vector50"
                  />
                  <img
                    src="/public/public/vector2882-m399.svg"
                    alt="Vector2882"
                    class="frame427322615-vector51"
                  />
                  <img
                    src="/public/public/vector2882-qduf.svg"
                    alt="Vector2882"
                    class="frame427322615-vector52"
                  />
                  <img
                    src="/public/public/vector2882-srxj.svg"
                    alt="Vector2882"
                    class="frame427322615-vector53"
                  />
                  <img
                    src="/public/public/vector2882-klx.svg"
                    alt="Vector2882"
                    class="frame427322615-vector54"
                  />
                  <img
                    src="/public/public/vector2882-1eek.svg"
                    alt="Vector2882"
                    class="frame427322615-vector55"
                  />
                  <img
                    src="/public/public/vector2882-211.svg"
                    alt="Vector2882"
                    class="frame427322615-vector56"
                  />
                  <img
                    src="/public/public/vector2882-3hhp.svg"
                    alt="Vector2882"
                    class="frame427322615-vector57"
                  />
                  <img
                    src="/public/public/vector2882-htze.svg"
                    alt="Vector2882"
                    class="frame427322615-vector58"
                  />
                  <img
                    src="/public/public/vector2882-u6ia.svg"
                    alt="Vector2882"
                    class="frame427322615-vector59"
                  />
                  <img
                    src="/public/public/vector2882-6xm.svg"
                    alt="Vector2882"
                    class="frame427322615-vector60"
                  />
                  <img
                    src="/public/public/vector2882-6rqk.svg"
                    alt="Vector2882"
                    class="frame427322615-vector61"
                  />
                  <img
                    src="/public/public/vector2882-18ef.svg"
                    alt="Vector2882"
                    class="frame427322615-vector62"
                  />
                  <img
                    src="/public/public/vector2882-2zeg.svg"
                    alt="Vector2882"
                    class="frame427322615-vector63"
                  />
                  <img
                    src="/public/public/vector2882-7c63.svg"
                    alt="Vector2882"
                    class="frame427322615-vector64"
                  />
                  <img
                    src="/public/public/vector2882-k49d.svg"
                    alt="Vector2882"
                    class="frame427322615-vector65"
                  />
                  <img
                    src="/public/public/vector2882-jd1r.svg"
                    alt="Vector2882"
                    class="frame427322615-vector66"
                  />
                  <img
                    src="/public/public/vector2882-lbw.svg"
                    alt="Vector2882"
                    class="frame427322615-vector67"
                  />
                  <div class="frame427322615-background23">
                    <span class="frame427322615-text309">Outstanding</span>
                  </div>
                </div>
                <div class="frame427322615-group53">
                  <div class="frame427322615-frame427322599">
                    <span class="frame427322615-text310">
                            {{$course->extraDetails->rate_title	}}
                    </span>
                    <div class="frame427322615-frame427322598">
@php
$rateTexts = $course->extraDetails ? json_decode($course->extraDetails->rate_options, true) ?? [] : [];
$rateIcons = $course->extraDetails ? json_decode($course->extraDetails->rate_icon, true) ?? [] : [];

// sirf pehle 3 items
$rateTexts = array_slice($rateTexts, 0, 3);
$rateIcons = array_slice($rateIcons, 0, 3);
@endphp

@foreach(array_map(null, $rateTexts, $rateIcons) as [$text, $icon])
    <div class="frame427322615-container20">
        <img
            src="{{ asset($icon) }}"
            alt="SVG2882"
            class="frame427322615svg29"
        />
        <span class="frame427322615-text311">
            {!! nl2br(e($text)) !!}
        </span>
    </div>
@endforeach

                    </div>
                    <div class="frame427322615-frame427322596">
                      <div class="frame427322615-container22">
                        <img
                          src="/public/public/svg2882-ki1o.svg"
                          alt="SVG2882"
                          class="frame427322615svg31"
                        />
                        <span class="frame427322615-text314">
                          Customer Support
                        </span>
                      </div>
                      <div class="frame427322615-container23">
                        <img
                          src="/public/public/svg2882-aixv.svg"
                          alt="SVG2882"
                          class="frame427322615svg32"
                        />
                        <span class="frame427322615-text315">
                          Knowledge Base
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="frame427322615-background24">
            <div class="frame427322615-frame427322601"></div>
            <div class="frame427322615-frame427322603">
              <div class="frame427322615-frame427322602">
                <span class="frame427322615-text316">
                  <span class="frame427322615-text317">
                   {{$course->extraDetails->ad_subtitle	}}
                </span>
                <span class="frame427322615-text319 mt-10">
                  {{$course->extraDetails->ad_title}}
                </span>
                <div class="frame427322615-group57 mt-10">
                  <!-- <div class="frame427322615-link26">
                     <a href="/subscriptions/direct-payment/asttrolok-pathshala" style="text-decoration: none;">
                    <span class="frame427322615-text320">Enroll Now</span>
</a>
                  </div> -->

              <form action="/cart/store" method="post" style="flex:1 1 0;">
                      @csrf
                      <input type="hidden" name="item_id" value="{{ $course->id }}">
                      <input type="hidden" name="item_name" value="webinar_id">
                      
                      @if($hasBought or !empty($course->getInstallmentOrder()))
    <button type="button" 
            class="frame427322615-button1 btn-primary" 
            onclick="window.location.href='{{ $course->getLearningPageUrl1() }}'">
        <span class="frame427322615-text320">
            Start Learning
        </span>
    </button>
@else
    <button type="button" 
            class="frame427322615-button1 btn-primary" 
            onclick="window.location.href='{{ $course->getLearningPageUrl1() }}'">
        <span class="frame427322615-text320">
            Enroll Now
        </span>
    </button>
@endif
                  </form>
                  <a href="/contact" style="text-decoration: none;" >
                  <div class="frame427322615-link27"style="@if($hasBought) left: 130px; @endif">
                    <span class="frame427322615-text321">Know more</span>
                  </div>
                  </a>
                </div>
              </div>
              <span class="frame427322615-text322 mt-10">
               {{$course->extraDetails->ad_description}}
              </span>
            </div>
            <div class="frame427322615-call-to-action-section"></div>
          </div>
            </div>
  <div class="beni">
    <div class="frame427322615-frame427322611">
        <div class="frame427322615-background-shadow " style="background-color: transparent !important;">
            <div class="frame427322615-background-border2">
                <div class="frame427322615-container11">
                   
     <div class="button-container"
     style="
        display:flex !important;
        flex-direction:column !important;
        gap:8px !important;
        width:100% !important;
        margin-top:24px;
     ">
    @php
        $canSale = ($course->canSale() and !$hasBought);
    @endphp
    <div class=" d-flex flex-column">
                                @if(!$canSale and $course->canJoinToWaitlist())
                                    <button type="button" data-slug="{{ $course->slug }}" class="btn btn-primary {{ (!empty($authUser)) ? 'js-join-waitlist-user' : 'js-join-waitlist-guest' }}">{{ trans('update.join_waitlist') }}</button>
                                @elseif($hasBought or !empty($course->getInstallmentOrder()))
<a href="{{ $course->getLearningPageUrl1() }}"
   class="enr btn btn-primary bbbbb"
   style="font-family: 'Inter', sans-serif !important;">
   Start Learning
</a>                                @elseif($course->price > 0)
                                    

                                    @if($canSale and $course->subscribe)
                                        <a href="/subscribes/apply/{{ $course->slug }}" class="btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                                    @endif

                                    @if($canSale and !empty($course->points))
                                        <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class="{{ (auth()->check()) ? 'js-buy-with-point' : '' }} btn btn-outline-warning mt-20 {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow">
                                            {!! trans('update.buy_with_n_points',['points' => $course->points]) !!}
                                        </a>
                                    @endif

                                    @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
                                    <form action="/cart/store" method="post" style="flex:1 1 0;">
                @csrf
                <input type="hidden" name="item_id" value="{{ $course->id }}">
                <input type="hidden" name="item_name" value="webinar_id">
                <button type="submit" class="frame427322615-button6 btn-success buy_now js-course-direct-payment">
                    <span class="frame427322615-text183">
                        Buy Now
                    </span>
                </button>
            </form>
                                       
                                    @endif

                                    @if(!empty($installments) and count($installments) and getInstallmentsSettings('display_installment_button'))
                                                        <a href="/course/{{ $course->slug }}/installments" class="frame427322615-button6 btn-success installment-button" style="margin-top:105px; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                                                             <span class="frame427322615-text183">
                                      {{ trans('update.pay_with_installments') }}
                                    </span>
                                                        </a>
                                    @endif
                               <form action="/cart/store" method="post" style="flex:1 1 0;">
        @csrf
        <input type="hidden" name="item_id" value="{{ $course->id }}">
        <input type="hidden" name="item_name" value="webinar_id">
       
    
                                <button type="submit" class="frame427322615-button62 btn-outline-danger {{ $canSale ? 'js-course-add-to-cart-btn' : ($course->cantSaleStatus($hasBought) .' disabled ') }}" >
                                        @if(!$canSale)
                                             <span class="frame427322615-text1834">
                                                Add to Cart
                                            </span>
                                        @else
                                            <span class="frame427322615-text1834">
                                            Add to Cart
                                        </span>
                                        @endif
                                    </button>
                                    </form>
                                    @else
                                    
                                    @if($course->slug == 'learn-free-vedic-astrology-course-online' )
                                        @if(empty($authUser))
                                            <a href="{{config('app.manual_base_url')}}/register-free" class="enr btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}" style="margin-top:110px;">{{ trans('public.enroll_on_webinar') }}</a>
                                        @else
                                            <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class="enr btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}" style="margin-top:110px;">{{ trans('public.enroll_on_webinar') }}</a>
                                        @endif
                                    @elseif($course->slug == 'learn-free-astrology-course-english')
                                        @if(empty($authUser))
                                            <a href="{{config('app.manual_base_url')}}/register-free-english" class="enr buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}" style="margin-top:110px;">{{ trans('public.enroll_on_webinar') }}</a>
                                        @else
                                            <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class="enr buy-btn1 btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}" style="margin-top:110px;">{{ trans('public.enroll_on_webinar') }}</a>
                                        @endif
                                    @else
                                    <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class="enr btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}" style="margin-top:110px;">{{ trans('public.enroll_on_webinar') }}</a>
                                    @endif
                                @endif
                            </div>

    <!-- Add to Wishlist Button -->
    <!-- <form action="/favorites/toggle" method="post" style="flex:1 1 0;">
        @csrf
        <input type="hidden" name="webinar_id" value="{{ $course->id }}">
        <button type="submit" class="frame427322615-button63 btn-outline-warning">
            <span class="frame427322615-text183s">
                Add to Wishlist
            </span>
        </button>
    </form> -->
    <style>
        .enr{
                top: 110px;
    left: 17px;
    width: 385px;
    height: 42px;
    display: flex;
    overflow: hidden;
    position: absolute;
    box-shadow: 0px 4px 6px -1px rgba(0, 0, 0, 0.10000000149011612);

    flex-shrink: 0;
    border-color: rgba(50, 160, 40, 1);
    border-style: solid;
    border-width: 1px;
    border-radius: 12px;
    background-color: rgba(50, 160, 40, 1);
}
        
    </style>
</div>

                </div>
                <span class="frame427322615-text184">Price</span>
                <img src="/public/public/svg1624-qvkc.svg" alt="SVG1624" class="frame427322615svg18"/>

                @php
                    $displayPrice = handleCoursePagePrice($course->price);
                @endphp

                  @if(!empty($activeSpecialOffer))
                       @php
                                                $priceWithDiscount = handleCoursePagePrice($course->getPrice());
                                            @endphp
                                              <span id="priceWithDiscount"
                                                  class="frame427322615-text188">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>
                      @else
                    <span class="frame427322615-text188">{{ $displayPrice['price'] }}  </span>
                    @endif

                <!-- <a href="/subscriptions/direct-payment/asttrolok-pathshala" style="text-decoration: none;">
                    <span class="frame427322615-text186">Pay Now</span>
                </a> -->
                        <form action="/cart/store" method="post" style="flex:1 1 0;">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $course->id }}">
                        <input type="hidden" name="item_name" value="webinar_id">
                        <button type="submit" class="frame427322615-text186 js-course-direct-payment f">
                            <span class="">
                                Pay Now
                            </span>
                        </button>
                    </form>
            

                <img src="/public/public/background1625-5hl-200h.png" alt="Background1625" class="frame427322615-background13"/>
                <img src="/public/public/horizontaldivider1625-gews-400w.png" alt="HorizontalDivider1625" class="frame427322615-horizontal-divider2"/>

                <span class="frame427322615-text187">Total Amount</span>
                 @if(!empty($activeSpecialOffer))
                       @php
                                                $priceWithDiscount = handleCoursePagePrice($course->getPrice());
                                            @endphp
                                              <span id="priceWithDiscount"
                                                  class="frame427322615-text188">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>
                      @else
                    <span class="frame427322615-text188">{{ $displayPrice['price'] }}  </span>
                    @endif

                <div class="frame427322615-background14">
                    <span class="mt-5" style="display:block; text-align:center !important;margin:auto;color:#1964B9;">
    Start at your Ease and Cancel Anytime
</span>

                </div>
            </div>

            <div class="frame427322615-background15">
                <img src="/public/public/svg1626-2g57.svg" alt="SVG1626" class="frame427322615svg19"/>
                <span class="frame427322615-text189">
 @if(!empty($activeSpecialOffer))
                       @php
                                                $priceWithDiscount = handleCoursePagePrice($course->getPrice());
                                            @endphp
                                              <span id="priceWithDiscount"
                                                  class="frame427322615-text190">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>
                      @else
                    <span class="frame427322615-text190">{{ $displayPrice['price'] }} Only </span>
                    @endif                    <br/>

                </span>
            </div>
        </div>
    </div>
</div>
          </div>

        </div>

      </div>

    </div>


    
<div class="modal fade " id="playVideo" tabindex="-1" aria-labelledby="playVideoLabel" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content py-20">
            <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line">Class 1 - Complete Guide to Aries</h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <div class="mt-25 position-relative">
                <div class="px-20">

                    <div class="js-modal-video-content">
                      <!-- <iframe src="https://iframe.mediadelivery.net/embed/759/eb1c4f77-0cda-46be-b47d-1118ad7c2ffe?autoplay=true" style="width:100%;height:400px;"> </iframe> -->
                      </div>
                </div>

                <div class="modal-video-lists mt-15">

                                    </div>
            </div>
        </div>
    </div>
   {{-- @endforeach --}}
</div>

@endsection

@push('scripts_bottom')

    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/owl-carousel2/owl.carousel.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/time-counter-down.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/barrating/jquery.barrating.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/videojs-youtube/3.0.1/Youtube.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/video/1212youtube.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/video/vimeo.js"></script>

    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/comment.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/video_player_helpers.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/webinar_show.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video.min.js"></script>
<script>
   document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('option1_btn');
    if (!btn) return;

    btn.addEventListener('click', function () {
        btn.classList.add('loadingbar','danger');
        btn.disabled = true;
        document.getElementById('option1_direct_form').submit();
    });
});

</script>
<script>
    $('#playVideo').on('hidden.bs.modal', function () {
    let iframe = $(this).find('iframe');
    let src = iframe.attr('src');
    iframe.attr('src', '');
    iframe.attr('src', src); // reset so video stops
});
</script>


@php
    Illuminate\Support\Facades\Session::forget('addtocart');
@endphp

    <script>
        var webinarDemoLang = '{{ trans('webinars.webinar_demo') }}';
        var replyLang = '{{ trans('panel.reply') }}';
        var closeLang = '{{ trans('public.close') }}';
        var saveLang = '{{ trans('public.save') }}';
        var reportLang = '{{ trans('panel.report') }}';
        var reportSuccessLang = '{{ trans('panel.report_success') }}';
        var reportFailLang = '{{ trans('panel.report_fail') }}';
        var messageToReviewerLang = '{{ trans('public.message_to_reviewer') }}';
        var copyLang = '{{ trans('public.copy') }}';
        var copiedLang = '{{ trans('public.copied') }}';
        var learningToggleLangSuccess = '{{ trans('public.course_learning_change_status_success') }}';
        var learningToggleLangError = '{{ trans('public.course_learning_change_status_error') }}';
        var notLoginToastTitleLang = '{{ trans('public.not_login_toast_lang') }}';
        var notLoginToastMsgLang = '{{ trans('public.not_login_toast_msg_lang') }}';
        var notAccessToastTitleLang = '{{ trans('public.not_access_toast_lang') }}';
        var notAccessToastMsgLang = '{{ trans('public.not_access_toast_msg_lang') }}';
        var canNotTryAgainQuizToastTitleLang = '{{ trans('public.can_not_try_again_quiz_toast_lang') }}';
        var canNotTryAgainQuizToastMsgLang = '{{ trans('public.can_not_try_again_quiz_toast_msg_lang') }}';
        var canNotDownloadCertificateToastTitleLang = '{{ trans('public.can_not_download_certificate_toast_lang') }}';
        var canNotDownloadCertificateToastMsgLang = '{{ trans('public.can_not_download_certificate_toast_msg_lang') }}';
        var sessionFinishedToastTitleLang = '{{ trans('public.session_finished_toast_title_lang') }}';
        var sessionFinishedToastMsgLang = '{{ trans('public.session_finished_toast_msg_lang') }}';
        var sequenceContentErrorModalTitle = '{{ trans('update.sequence_content_error_modal_title') }}';
        var courseHasBoughtStatusToastTitleLang = '{{ trans('cart.fail_purchase') }}';
        var courseHasBoughtStatusToastMsgLang = '{{ trans('site.you_bought_webinar') }}';
        var courseNotCapacityStatusToastTitleLang = '{{ trans('public.request_failed') }}';
        var courseNotCapacityStatusToastMsgLang = '{{ trans('cart.course_not_capacity') }}';
        var courseHasStartedStatusToastTitleLang = '{{ trans('cart.fail_purchase') }}';
        var courseHasStartedStatusToastMsgLang = '{{ trans('update.class_has_started') }}';
        var joinCourseWaitlistLang = '{{ trans('update.join_course_waitlist') }}';
        var joinCourseWaitlistModalHintLang = "{{ trans('update.join_course_waitlist_modal_hint') }}";
        var joinLang = '{{ trans('footer.join') }}';
        var nameLang = '{{ trans('auth.name') }}';
        var emailLang = '{{ trans('auth.email') }}';
        var phoneLang = '{{ trans('public.phone') }}';
        var captchaLang = '{{ trans('site.captcha') }}';
    </script>
<script>

</script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/comment.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/video_player_helpers.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/webinar_show.min.js"></script>
    <script type="text/javascript" src="https://asttrolok.in/asttroloknew/assets/design_1/js/app.min.js"></script>
        <script src="https://asttrolok.in/asttroloknew/assets/vendors/wrunner-html-range-slider-with-2-handles/js/wrunner-jquery.js"></script>
    <script src="https://asttrolok.in/asttroloknew/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="https://asttrolok.in/asttroloknew/assets/design_1/js/parts/range_slider_helpers.min.js"></script>
    <script src="https://asttrolok.in/asttroloknew/assets/design_1/js/parts/swiper_slider.min.js"></script>
@endpush
