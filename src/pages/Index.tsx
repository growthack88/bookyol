import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import FloatingIcons from "@/components/FloatingIcons";
import logo from "@/assets/bookyol-logo.png";

const Index = () => {
  const [email, setEmail] = useState("");
  const [submitted, setSubmitted] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (email) {
      setSubmitted(true);
    }
  };

  return (
    <div className="relative h-screen w-screen overflow-hidden bg-gradient-to-br from-background via-background to-secondary">
      <FloatingIcons />
      
      {/* Center content */}
      <div className="relative z-10 flex h-full w-full items-center justify-center px-6">
        <div className="flex flex-col items-center text-center">
          {/* Logo */}
          <img 
            src={logo} 
            alt="BookYol.com - Read. Discover. Escape." 
            className="mb-8 h-auto w-64 sm:w-80 md:w-96"
          />
          
          {/* Headline */}
          <h1 className="mb-3 text-3xl font-semibold tracking-tight text-foreground sm:text-4xl">
            Coming Soon
          </h1>
          
          {/* Subtext */}
          <p className="mb-8 max-w-md text-base text-muted-foreground sm:text-lg">
            Something special for readers is on the way.
          </p>
          
          {/* Email capture */}
          {!submitted ? (
            <form 
              onSubmit={handleSubmit}
              className="flex w-full max-w-sm flex-col gap-3 sm:flex-row"
            >
              <Input
                type="email"
                placeholder="Enter your email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="h-11 flex-1 border-border bg-card/80 backdrop-blur-sm"
              />
              <Button 
                type="submit"
                className="h-11 px-6 font-medium"
              >
                Notify Me
              </Button>
            </form>
          ) : (
            <div className="rounded-lg border border-primary/20 bg-primary/5 px-6 py-3">
              <p className="text-sm font-medium text-primary">
                Thank you! We'll keep you posted.
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default Index;
