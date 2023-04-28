@extends('layouts.main')
@section('content')
    <h2 class="text-center">Search Flights</h2>
    <form action="/api/search" class="mt-3" method="POST">
        @csrf
        <div class="row">
            <div class="form-group col-6">
                <input type="text" class="form-control" id="search-from" placeholder="From" required>
                <input type="hidden" name="from" id="hidden-from">
                <div id="search-results-from">
                    <ul id="from-list"></ul>
                </div>

            </div>
            <div class="form-group col-6">
                <input type="text" class="form-control" id="search-to" placeholder="To" required>
                <input type="hidden" name="to" id="hidden-to">
                <div id="search-results-to">
                    <ul id="to-list"></ul>
                </div>

            </div>
            <div class="form-group col-6 mt-3">
                <input type="date" class="form-control" placeholder="Departure Date" name="date" required>
            </div>
            <div class="form-group col-6 mt-3">
                <input type="number" class="form-control" placeholder="Passengers" name="passengers" required>
            </div>
            <div class="form-group col-12 mt-3">
                <button class="btn btn-primary form-control" type="submit">Search</button>
            </div>
        </div>
    </form>

    {{-- Results section --}}

    @if (isset($flights) && count($flights) > 0)
        <h2 class="text-center mt-5">{{ count($flights) }} Results</h2>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Price (EUR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($flights as $flight)
                    @php
                        //dd($flight);
                        // $new_flight = [
                        //     'type' => $flight->type,
                        //     'id' => $flight->id,
                        //     'source' => $flight->source,
                        //     'instantTicketingRequired' => $flight->instantTicketingRequired,
                        //     'nonHomogeneous' => $flight->nonHomogeneous,
                        //     'oneWay' => $flight->oneWay,
                        //     'lastTicketingDate' => $flight->lastTicketingDate,
                        //     'lastTicketingDateTime' => $flight->lastTicketingDateTime,
                        //     'numberOfBookableSeats' => $flight->numberOfBookableSeats,
                        //     'itineraries' => $flight->itineraries[0],
                        //     'price' => $flight->price->total,
                        //     'pricingOptions' => $flight->pricingOptions,
                        //     'validatingAirlineCodes' => $flight->validatingAirlineCodes,
                        //     'travelerPricings' => $flight->travelerPricings[0],
                        // ];
                    @endphp
                    <tr onclick="document.getElementById('{{ 'form' . $flight->id }}').submit()" style=”cursor: pointer;”>
                        <th scope="row">{{ $flight->id }}</th>
                        <td>{{ $flight->itineraries[0]->duration }}</td>
                        <td>{{ $flight->price->total }}</td>
                    </tr>
                    <form action="/api/price" hidden id="{{ 'form' . $flight->id }}" method="POST">
                        @csrf
                        <input type="hidden" name="flight" value="{{ json_encode($flight) }}">
                        {{-- <input type="hidden" name="new_flight" value="{{ json_encode($new_flight) }}"> --}}
                    </form>
                @endforeach
            </tbody>
        </table>
    @endif

@endsection

@section('scripts')
    <script>
        function getAuthorization() {
            return new Promise((resolve, reject) => {               
                $.ajax({
                    url: "{{ route('init') }}",
                    type: "GET",
                    //data: data,
                    success: function(response) {                      
                        //console.log(response);
                        const data = JSON.parse(response);                        
                        resolve(data.access_token);
                    },
                    error: function(error) {
                        console.log("Access Token Error: ",error);
                        reject(error);
                    }
                });
            });
        }




        $(document).ready(function() {
            getAuthorization()
                .then((access_token) => {
                    //console.log("Access Token: " + access_token);

                    const apiUrl = "https://test.api.amadeus.com/v1/";

                    function searchLocations(query, position) {
                        $.ajax({
                            url: `${apiUrl}reference-data/locations?subType=CITY&keyword=${query}`,
                            type: "GET",
                            headers: {
                                "Authorization": `Bearer ${access_token}`
                            },
                            success: function(response) {
                                console.log("Locations ",response);
                                let results = "";
                                response.data.forEach(function(location) {
                                    results +=
                                        `<div class="result" data-iata="${location.iataCode}" data-displayname="${location.name}">${location.name}</div>`;
                                });
                                if (position == "from") {
                                    $("#search-results-from").html(results);
                                }
                                if (position == "to") {
                                    $("#search-results-to").html(results);
                                }
                                $(".result").on("click", function() {
                                    const iata = $(this).data("iata");
                                    const displayName = $(this).data("displayname");
                                    const hiddenFieldFrom = $("#hidden-from");
                                    const hiddenFieldTo = $("#hidden-to");
                                    const searchFrom = $("#search-from");
                                    const searchTo = $("#search-to");

                                    if (position == 'from') {
                                        hiddenFieldFrom.val(iata);
                                        searchFrom.val(displayName);
                                    }

                                    if (position == 'to') {
                                        hiddenFieldTo.val(iata);
                                        searchTo.val(displayName);
                                    }
                                    $(this).parent().html("");
                                });
                            },
                            error: function(error) {
                                console.log(error);
                            }
                        });
                    }



                    $("#search-from").on("keyup", function() {
                        const query = $(this).val().trim();
                        const position = "from";
                        //alert(query);
                        if (query.length >= 3) {
                            searchLocations(query, position);
                        } else {
                            $("#search-results-from").html("");
                        }
                    });

                    $("#search-to").on("keyup", function() {
                        const query = $(this).val().trim();
                        const position = "to";
                        if (query.length >= 3) {
                            searchLocations(query, position);
                        } else {
                            $("#search-results-to").html("");
                        }
                    });
                });
        });
    </script>
@endsection
