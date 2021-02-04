@extends('owner.master')
@section('main_content')
    <div class="ms-content-wrapper">
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="ms-card ms-widget has-graph-full-width ms-infographics-widget">

                  <div class="ms-card-body media">
                    <div class="media-body">
                      <span class="black-text"><strong>Today's Sell(unit)</strong></span>
                      <h2>{{ $todays_sell_unit }}</h2>
                    </div>
                  </div>
                  <canvas id="line-chart"></canvas>
                </div>
              </div>

              <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="ms-card ms-widget has-graph-full-width ms-infographics-widget">

                  <div class="ms-card-body media">
                    <div class="media-body">
                      <span class="black-text"><strong>Today's Sell(amount)</strong></span>
                      <h2>{{ $todays_sell_amount }}</h2>
                    </div>
                  </div>
                  <canvas id="line-chart-2"></canvas>
                </div>
              </div>

              <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="ms-card ms-widget has-graph-full-width ms-infographics-widget">

                  <div class="ms-card-body media">
                    <div class="media-body">
                      <span class="black-text"><strong>Monthly Sell(unit)</strong></span>
                      <h2>{{ $monthly_sell_unit }}</h2>
                    </div>
                  </div>
                  <canvas id="line-chart-3"></canvas>
                </div>
              </div>

              <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="ms-card ms-widget has-graph-full-width ms-infographics-widget">

                  <div class="ms-card-body media">
                    <div class="media-body">
                      <span class="black-text"><strong>Montly SELL (amount)</strong></span>
                      <h2>{{ $monthly_sell_amount }}</h2>
                    </div>
                  </div>
                  <canvas id="line-chart-4"></canvas>
                </div>
              </div>




        </div>
    </div>
@endsection
