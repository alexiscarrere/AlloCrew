import Announcement from '../components/Announcement';
import {fetchAnnouncement} from '../Redux/actions'
import {connect} from 'react-redux';

const mapStateToProps = ({data}) => {
  const announcement = data.announcements[0];
  return({
    title: announcement.title,
    location: announcement.location,
    description: announcement.description,
    picture: announcement.picture,
    voluntary: announcement.voluntary,
    id: announcement.id,
    dateStart: announcement.dateStart,
    dateEnd: announcement.dateEnd,
    active: announcement.active,
    user: announcement.user
  })
};

const mapDispatchToProps = (dispatch, {match}) => ({
  fetchData: dispatch(fetchAnnouncement(match.params.id))
})
;

const announcement = connect(mapStateToProps, mapDispatchToProps)(Announcement);

export default announcement;