import React, {Component} from 'react';
import {Image, Platform} from 'react-native';
import MapView, {
  ProviderPropType,
  Marker,
  Callout,
  AnimatedRegion,
} from 'react-native-maps';
import Images from '../../assets';
import {getLeadsForMap, clearMapData} from '../../actions';
import {connect} from 'react-redux';
import {Toasts} from '../../Utility/showToast';
import {Container} from 'native-base';
import {SpinnerView} from '../../Utility/common/SpinnerView';
import haversine from 'haversine';
import LocationPicker from '../../Utility/LocationPicker';

class LeadsMap extends Component {
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
    trackViewChanges: true,
  };

  componentDidMount() {
    LocationPicker.checkAndRequestLocation(
      this._onGetLocationSuccess,
      this._onGetLocationFailure,
    );
    if (!__DEV__) {
      this.props.getLeadsForMap(1, null, null, true);
    }
  }
  _onGetLocationSuccess = location => {
    const {latitude, longitude} = location.coords;
    const newCoordinate = {
      latitude,
      longitude,
    };

    this.setState({
      prevLatLng: newCoordinate,
      currLat: latitude.toString(),
      currLong: longitude.toString(),
    });
    console.log('CURRENT', location.coords);
    //Api to load leads
    // this.props.getLeadsForMap(1, null, null, true);
  };

  _onGetLocationFailure = error => {
    const {message} = error;
    console.log('RRr', error);
    Toasts.showToast(message);
  };

  componentDidUpdate() {
    if (this.state.changeFocus) {
      let markerIDs = [];
      if (this.props.error !== '') {
        Toasts.showToast(this.props.error);
      } else if (this.props.mapLeadList.length > 0) {
        this.props.mapLeadList.map(lead => markerIDs.push(String(lead.id)));
        // this.focusMap(markerIDs);
        //set focus false
        this.setState({
          changeFocus: false,
        });
        setTimeout(() => {
          this.focusMap(markerIDs);
        }, 1000);
      } else if (this.props.mapLeadList.length === 0) {
        this.focusMap(['1']);
      }
    }
  }
  focusMap(markers) {
    const options = {
      edgePadding: {
        top: 350,
        right: 350,
        bottom: 350,
        left: 350,
      },
      animated: true,
    };
    if (this.mapRef !== null) {
      this.mapRef.fitToSuppliedMarkers(markers, options);
      setTimeout(() => {
        //set change region
        this.setState({
          onChangeRegion: false,
          trackViewChanges: false,
        });
      }, 3000);
    }
  }
  _onPressMarker = (laed, index) => {
    const {currLat, currLong} = this.state;
    this.props.navigation.navigate('summary', {
      leadDetail: laed,
      index,
      type: 'mapLeadList',
      currLatLong: currLat + ',' + currLong,
    });
  };
  calcDistance = newLatLng => {
    const {prevLatLng} = this.state;
    if (haversine(prevLatLng, newLatLng, {unit: 'mile'}) > 50) {
      this.setState({
        prevLatLng: newLatLng,
      });
      //   this.props.getLeadsForMap(
      //     1,
      //     newLatLng.latitude,
      //     newLatLng.longitude,
      //     true,
      //   );
    }
  };
  render() {
    const {mapLeadList} = this.props;
    const {trackViewChanges} = this.state;
    return (
      <Container>
        <MapView
          ref={ref => (this.mapRef = ref)}
          onRegionChangeComplete={region => {
            const {latitude, longitude} = region;
            const newCoordinate = {
              latitude,
              longitude,
            };
            if (!this.state.onChangeRegion) {
              this.calcDistance(newCoordinate);
            }
          }}
          showsUserLocation={true}
          loadingEnabled
          // initialRegion={this.state.region}
          //onPoiClick={true}
          style={{flex: 1}}>
          {mapLeadList.length === 0
            ? null
            : mapLeadList.map((leads, index) => {
                return (
                  <Marker
                    onPress={() => this._onPressMarker(leads, index)}
                    key={index}
                    tracksViewChanges={trackViewChanges}
                    coordinate={leads.coordinate}
                    identifier={String(leads.id)}>
                    <Image
                      source={Images.ic_homes}
                      style={{
                        height: 50,
                        width: 40,
                        tintColor: leads.status.color_code,
                      }}
                    />
                  </Marker>
                  //     Platform.OS === 'android' ?
                  //         <Marker
                  //             onPress={() => this._onPressMarker(leads, index)}
                  //             key={index}
                  //             coordinate={leads.coordinate}
                  //             identifier={String(leads.id)}>
                  //             <Image source={Images.ic_homes}
                  //                 style={{ height: 50, width: 40, tintColor: leads.status.color_code }} />
                  //         </Marker>
                  //         :
                  //         <Marker
                  //             onPress={() => this._onPressMarker(leads, index)}
                  //             key={index}
                  //             coordinate={leads.coordinate}
                  //             image={Images["ic_" + leads.status.color_code.replace(/^#+/, '')]}
                  //             identifier={String(leads.id)}>
                  //             {/* <Image source={Images.ic_homes}
                  //             style={{ height: heightRatio(90), width: widthRatio(70), tintColor: leads.status.color_code }} /> */}
                  //         </Marker>
                );
              })}
        </MapView>
        {this.props.loading && <SpinnerView />}
      </Container>
    );
  }
}
const mapStateToPrps = state => {
  return {
    mapLeadList: state.leads.mapLeadList,
    error: state.leads.error,
    refreshing: state.leads.refreshing,
    loading: state.leads.loading,
    // nextPage: state.leads.nextPage,
    // currentPage: state.leads.currentPage
  };
};
export default connect(mapStateToPrps, {
  getLeadsForMap,
  clearMapData,
})(LeadsMap);
