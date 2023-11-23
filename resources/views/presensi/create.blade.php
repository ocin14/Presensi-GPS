@extends('layouts.presensi')
@section('header')
    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">E-presensi</div>
        <div class="right"></div>
    </div>
    <!-- * App Header -->
    <style>
        .webcamcapture,
        .webcamcapture video{
            display: inline-block;
            width: 100% !important;
            margin: auto;
            height: auto !important;
            border-radius: 15px;
        }
        #map { 
            height: 200px; 
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection
@section('content')
<div class="row" style="margin-top: 70px">
    <div class="col">
        <input type="hidden" id="lokasi">
        <div class="webcamcapture">

        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        @if ($cek > 0)
        <button id="takeabsen" class="btn btn-danger btn-block"><ion-icon name="camera-outline"></ion-icon>Absen pulang
        </button>
        @else
        <button id="takeabsen" class="btn btn-primary btn-block"><ion-icon name="camera-outline"></ion-icon>Absen masuk
        </button>
        @endif
   </div>
</div>
<div class="row mt-2">
    <div class="col">
        <div id="map"></div>
    </div>
</div>
<audio id="notifin">
    <source src="{{ asset('assets/audio_notif/notifin.mp3') }}" type="audio/mpeg">
</audio>
<audio id="notifout">
    <source src="{{ asset('assets/audio_notif/notifout.mp3') }}" type="audio/mpeg">
</audio>
<audio id="luarradius">
    <source src="{{ asset('assets/audio_notif/luarradius.mp3') }}" type="audio/mpeg">
</audio>
@endsection

@push('myscript')
    <script>
        var notifin = document.getElementById('notifin');
        var notifout = document.getElementById('notifout');
        var luarradius = document.getElementById('luarradius');
        Webcam.set({
            height: 480,
            width: 640,
            image_format:'jpeg',
            jpeg_quality: 80
        });
        Webcam.attach('.webcamcapture');

        var lokasi = document.getElementById('lokasi');
        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(successCallback, errorCallback);

        }
        function successCallback(position){
            lokasi.value = position.coords.latitude + ","+position.coords.longitude;
            var map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 18);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);
    var circle = L.circle([-7.975029309135222, 112.66474269269797], {
    color: 'red',
    fillColor: '#f03',
    fillOpacity: 0.5,
    radius: 20
}).addTo(map);
        }

        function errorCallback(){

        }

        $("#takeabsen").click(function(e){
            Webcam.snap(function(uri){
                image = uri;
            });
            var lokasi = $("#lokasi").val();
            $.ajax({
                type:'POST',
                url: '/presensi/store',
                data:{
                    _token:"{{ csrf_token () }}",
                    image:image,
                    lokasi:lokasi
                },
                cache:false,
                success:function(respond){
                    var status = respond.split("|");
                    if( status[0] = "success"){
                        if(status[2] = "in"){
                            notifin.play();
                        }else{
                            notifout.play();
                        }
                        Swal.fire({
                     title: 'Berhasil',
                     text: status[1],
                     icon: 'success'
                    })
                    setTimeout("location.href='/dashboard'", 3000);
                    }else{
                        if(status[2] = "luarradius"){
                            luarradius.play();
                        }
                        Swal.fire({
                     title: 'Error!',
                     text: status[1],
                     icon: 'error'
                    })
                    }
                }
            });
        });
    </script>
@endpush