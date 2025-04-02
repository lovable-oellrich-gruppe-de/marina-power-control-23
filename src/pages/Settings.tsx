
import { DatabaseConfig } from "@/components/settings/DatabaseConfig";

const Settings = () => {
  return (
    <div className="container py-8">
      <h1 className="text-3xl font-bold mb-6 text-marina-800">Einstellungen</h1>
      
      <div className="space-y-8">
        <DatabaseConfig />
      </div>
    </div>
  );
};

export default Settings;
