import React from 'react';
import './style.scss';
import Profile from './Profile';

const ProfileList = ({ list }) => (
  <div className="profilelist__container">
    {/* Bogdan Codes here */}
    {
      list.map((profile) => <Profile {...profile}/>)
    }
  </div>
)
;

export default ProfileList; 