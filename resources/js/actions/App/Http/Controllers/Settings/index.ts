import ProfileController from './ProfileController'
import BusinessController from './BusinessController'
import ReportController from './ReportController'
import SecurityController from './SecurityController'

const Settings = {
    ProfileController: Object.assign(ProfileController, ProfileController),
    BusinessController: Object.assign(BusinessController, BusinessController),
    ReportController: Object.assign(ReportController, ReportController),
    SecurityController: Object.assign(SecurityController, SecurityController),
}

export default Settings