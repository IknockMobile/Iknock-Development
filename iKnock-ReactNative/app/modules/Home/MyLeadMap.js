import React, { Component } from 'react';
import { Image, Platform } from 'react-native';
import MapView, { ProviderPropType, Marker, Callout, AnimatedRegion } from 'react-native-maps';
import Images from '../../assets';
import { clearMapData, getMyLeadsForMap } from '../../actions';
import { connect } from 'react-redux';
import { Toasts } from "../../Utility/showToast";
import { Container } from 'native-base';
import { SpinnerView } from '../../Utility/common/SpinnerView';
import haversine from "haversine";
import LocationPicker from "../../Utility/LocationPicker";

class MyLeadMap extends Component {

    state = {
        changeFocus: true,
        onChangeRegion: true,
        region: {
            latitude: 45.52220671242907,
            longitude: -122.6653281029795,
            latitudeDelta: 0.04864195044303443,
            longitudeDelta: 0.040142817690068,
        },
        prevLatLng: {},
        currLat: '',
        currLong: '',
    };

    componentDidMount() {
        LocationPicker.checkAndRequestLocation(this._onGetLocationSuccess, this._onGetLocationFailure);
        // //on back refresh
        // this.didFocusListener = this.props.navigation.addListener(
        //     'didFocus',
        //     () => {
        //         LocationPicker.checkAndRequestLocation(this._onGetLocationSuccess, this._onGetLocationFailure);
        //     },
        // );
    }
    _onGetLocationSuccess = (location) => {
        const { latitude, longitude } = location.coords;
        const newCoordinate = {
            latitude,
            longitude
        };
        this.setState({
            prevLatLng: newCoordinate,
            currLat: latitude.toString(),
            currLong: longitude.toString(),
        });

        //Api to load leads
        this.props.getMyLeadsForMap(1, latitude, longitude, true);
    }
    _onGetLocationFailure = (error) => {
        const { message } = error;
        Toasts.showToast(message);
    }

    componentDidUpdate() {
        if (this.state.changeFocus) {

            let markerIDs = [];
            if (this.props.error !== '') {
                Toasts.showToast(this.props.error);

            } else if (this.props.mapMyLeadList.length > 0) {
                this.props.mapMyLeadList.map((lead) =>
                    markerIDs.push(String(lead.id))
                );
                // this.focusMap(markerIDs);
                //set focus false
                this.setState({
                    changeFocus: false
                });
                setTimeout(() => {
                    this.focusMap(markerIDs);
                }, 1000);
            }
            if (this.props.mapMyLeadList.length === 0) {
                this.focusMap(["1"]);
                // this.setState({
                //     changeFocus: false
                // });
            }
        }
    }
    focusMap(markers) {
        options = {
            edgePadding: {
                top: 350,
                right: 350,
                bottom: 350,
                left: 350,
            },
            animated: true
        }
        if (this.mapRef !== null) {
            this.mapRef.fitToSuppliedMarkers(markers, options);
            setTimeout(() => {
                //set change region
                this.setState({
                    onChangeRegion: false
                });
            }, 2000)
        }
    }
    _onPressMarker = (laed, index) => {
        const { currLat, currLong } = this.state;
        this.props.navigation.navigate('summary',
            {
                'leadDetail': laed,
                index,
                'type': 'myMapLeadList',
                'currLatLong': currLat + ',' + currLong
            });
    }
    calcDistance = (newLatLng) => {
        const { prevLatLng } = this.state;
        if (haversine(prevLatLng, newLatLng, { unit: 'mile' }) > 50) {
            this.setState({
                prevLatLng: newLatLng
            });
            this.props.getMyLeadsForMap(1, newLatLng.latitude, newLatLng.longitude, true, this.props.type);
        }
    };
    render() {
        const { mapMyLeadList } = this.props;
        return (
            <Container>
                <MapView
                    ref={(ref) => this.mapRef = ref}
                    onRegionChangeComplete={(region) => {
                        const { latitude, longitude } = region
                        const newCoordinate = {
                            latitude,
                            longitude
                        };
                        if (!this.state.onChangeRegion) {
                            this.calcDistance(newCoordinate);
                        }
                    }}
                    showsUserLocation={true}
                    loadingEnabled
                    // initialRegion={this.state.region}
                    //onPoiClick={true}
                    style={{ flex: 1 }}
                >
                    {mapMyLeadList.length === 0 ? null : mapMyLeadList.map((leads, index) => {
                        return (
                            <Marker
                                onPress={() => this._onPressMarker(leads, index)}
                                key={index}
                                coordinate={leads.coordinate}
                                identifier={String(leads.id)}>
                                <Image source={Images.ic_homes}
                                    style={{ height: 50, width: 40, tintColor: leads.status.color_code }} />
                            </Marker>
                            // Platform.OS === 'ios' ?
                            //     <Marker
                            //         onPress={() => this._onPressMarker(leads, index)}
                            //         key={index}
                            //         coordinate={leads.coordinate}
                            //         identifier={String(leads.id)}>
                            //         <Image source={Images.ic_homes}
                            //             style={{ height: 50, width: 40, tintColor: leads.status.color_code }} />
                            //     </Marker>
                            //     :
                            //     <Marker
                            //         onPress={() => this._onPressMarker(leads, index)}
                            //         key={index}
                            //         coordinate={leads.coordinate}
                            //         image={Images["ic_" + leads.status.color_code.replace(/^#+/, '')]}
                            //         identifier={String(leads.id)}>
                            //         {/* <Image source={Images.ic_homes}
                            //         style={{ height: heightRatio(90), width: widthRatio(70), tintColor: leads.status.color_code }} /> */}
                            //     </Marker>
                        );
                    })}
                </MapView>
                {this.props.loading && <SpinnerView />}
            </Container>
        )
    }
}
const mapStateToPrps = (state) => {
    return {

        mapMyLeadList: state.leads.mapMyLeadList,
        error: state.leads.error,
        refreshing: state.leads.refreshing,
        loading: state.leads.loading,
        nextPage: state.leads.nextPage,
        currentPage: state.leads.currentPage
    };
};
export default connect(mapStateToPrps, {

    getMyLeadsForMap,
    clearMapData
})(MyLeadMap);